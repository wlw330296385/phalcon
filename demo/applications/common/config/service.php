<?php
use Phalcon\Di;
use Phalcon\Tag;
use Phalcon\Mvc\Url;
use Phalcon\escaper;
use Phalcon\Http\Response;
use Phalcon\Http\Request;
use Phalcon\Mvc\Application;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Volt;
use Phalcon\Db\Adapter\Pdo\Mysql as DB;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Mvc\Dispatcher as MvcDispatcher;
use Phalcon\Db\Profiler as DbProfiler;
use Phalcon\Logger;
use Phalcon\Logger\Formatter\Line as LineFormatter;
use Phalcon\Logger\Adapter\File as FileAdapter;
use Phalcon\Mvc\Model\Transaction\Manager as TransactionManager;
use Phalcon\Text;


$di = new DI();
date_default_timezone_set('Asia/Shanghai');
$di->set('config', $config);
$di->set('response', 'Phalcon\Http\Response');
$di->set('request', 'Phalcon\Http\Request');
$di->set('modelsMetadata', 'Phalcon\Mvc\Model\Metadata\Memory');
$di->set('modelsManager', 'Phalcon\Mvc\Model\Manager');
$di->set('filter', 'Phalcon\Filter');
$di->set('url', function () use ($di,$config) {
    $url = new Url();
    $base_url = strip_tags($di->get('request')->getServer('SCRIPT_NAME'));
    if ($config->rewrite) {
        if($base_url == '/index.php'){
            $url->setBaseUri('/');
        }else{
            $url->setBaseUri("{$base_url}?_url=/");
        }
    }else{
        $url->setBaseUri("{$base_url}?_url=/");
    }
    return $url;
});
$di->set('tag', function () {
    $tag = new Tag();
    return $tag;
});

$di->set('escaper', function () {
    $EsCaper = new escaper();
    return $EsCaper;
});

$di->setShared('transactions', function () {
    return new TransactionManager();
});

$di->set('logger', function () use ($config) {
    if (!is_dir($config->application->loggerDir)) {
        mkdir($config->application->loggerDir, 0777, true);
    }
    $filename = $config->application->loggerDir . '/runtime.log';
    $formatter = new LineFormatter("%date%||%type%||%message%");
    $formatter->setDateFormat("Y-m-d H:i:s");
    $logger = new FileAdapter($filename);
    $logger->setFormatter($formatter);
    return $logger;
});

$di->set('modelsManager', function () {
    $modelsManager = new Phalcon\Mvc\Model\Manager();
    return $modelsManager;
});
$di->set('modelsCache', function () use ($config) {
    $cacheConfig = (array)$config->cache;

    $cacheConfig['prefix'] = sprintf(':%s:%s:',PROJECT_NAME,strtoupper(APP_NAMESPACE));
    $cacheData = new Phalcon\Cache\Frontend\Data(array('lifetime' => $cacheConfig['lifetime']));
    if ($cacheConfig['adapter'] == 'Redis') {
        $cache = new Phalcon\Cache\Backend\Redis($cacheData, $cacheConfig);
    } else if ($cacheConfig['adapter'] == 'Memcache') {
        $cache = new Phalcon\Cache\Backend\Memcache($cacheData, $cacheConfig);
    } else {
        if (!is_dir($config->application->dataCacheDir . '_fields')) {
            mkdir($config->application->dataCacheDir . '_fields', 0777, true);
        }
        $cache = new Phalcon\Cache\Backend\File($cacheData, array('cacheDir' => $config->application->dataCacheDir));
    }
    return $cache;
});
$di->set('dispatcher', function () {
    $app_namespace = sprintf('Oupula\%s\Controllers', APP_NAMESPACE);
    $eventsManager = new EventsManager();
    $eventsManager->attach("dispatch:beforeDispatchLoop", function ($event, $dispatcher) {
        /**@var $dispatcher MvcDispatcher */
        $action = basename($dispatcher->getActionName(), '.html');
        $dispatcher->setControllerName(Text::uncamelize($dispatcher->getControllerName()));
        $dispatcher->setActionName($action);
    });
    $dispatcher = new MvcDispatcher();
    $dispatcher->setDefaultNamespace($app_namespace);
    $dispatcher->setEventsManager($eventsManager);
    return $dispatcher;
});
$di->set('view', function () use ($config, $di) {
    $view = new View();
    $view->setViewsDir($config->application->viewsDir);
    $view->registerEngines(
        array('.html' => function ($view, $di) use ($config) {
            $volt = new Volt($view, $di);
            $volt->setOptions(array(
                'compiledPath' => function ($templatePath) use ($config) {
                    if (!is_dir($config->application->viewsCacheDir)) {
                        mkdir($config->application->viewsCacheDir, 0777, true);
                    }
                    return $config->application->viewsCacheDir . md5($templatePath) . '.php';
                }
            ));
            $compiler = $volt->getCompiler();
            $di->set('compiler',$compiler);
            $compiler->addFunction('str_array', function ($resolvedArgs, $exprArgs) use ($compiler) {
                $data = $compiler->expression($exprArgs[0]['expr']);
                $needle = $compiler->expression($exprArgs[1]['expr']);
                $checked = $compiler->expression($exprArgs[2]['expr']);
                return "in_array($needle,explode(',',$data)) ? {$checked} : ''";
            });
            $compiler->addFunction('str_cut', function ($resolvedArgs, $exprArgs) use ($compiler) {
                $content = $compiler->expression($exprArgs[0]['expr']);
                $length = $compiler->expression($exprArgs[1]['expr']);
                return "mb_strcut($content,0,$length)";
            });
            return $volt;
        })
    );
    return $view;
});

$di->set('assets', function () {
    $assets = new Phalcon\Assets\Manager();
    return $assets;
});

$di->set('session', function () use ($config) {
    $sessionPrefix = sprintf('%s:%s:session:',PROJECT_NAME,strtoupper(APP_NAMESPACE));
    $sessionName = sprintf("%s_session",ucfirst(APP_NAMESPACE));
    $cacheConfig = get_object_vars($config->cache);
    $cacheConfig['prefix'] = "_{$sessionPrefix}_session_";
    if ($cacheConfig['adapter'] == 'Redis') {
        $session = new Phalcon\Session\Adapter\Redis($cacheConfig);
    } else if ($cacheConfig['adapter'] == 'Memcache') {
        $session = new Phalcon\Session\Adapter\Memcache($cacheConfig);
    } else {
        $session = new Phalcon\Session\Adapter\Files();
    }
    $session->setName($sessionName);
    $session->start();
    return $session;
});




$di->set('db', function () use ($config) {
    $connection = new DB(array(
        "host" => $config->database->host,
        "username" => $config->database->username,
        "password" => $config->database->password,
        "dbname" => $config->database->dbname,
        "charset" => $config->database->charset,
    ));
    if ($config->debug) {
        if (!is_dir($config->application->loggerDir)) {
            mkdir($config->application->loggerDir, 0777, true);
        }
        $filename = $config->application->loggerDir . '/sql.log';
        $profiler = new DbProfiler();
        $eventsManager = new EventsManager();
        $logger = new FileAdapter($filename);
        $eventsManager->attach('db', function ($event, $connection) use ($logger, $profiler) {
            /**@var $connection DB * */
            /**@var $event EventsManager * */
            if ($event->getType() == 'beforeQuery') {
                $profiler->startProfile($connection->getSQLStatement());
            }
            if ($event->getType() == 'afterQuery') {
                // 操作后停止分析
                $profiler->stopProfile();
                $profile = $profiler->getLastProfile();
                $logger->log("================================== SQL DEBUG ========================================", Logger::INFO);
                $logger->log("SQL Statement: " . $profile->getSQLStatement(), Logger::INFO);
                $logger->log("Start Time: " . $profile->getInitialTime(), Logger::INFO);
                $logger->log("Final Time: " . $profile->getFinalTime(), Logger::INFO);
                $logger->log("Total Elapsed Time: " . $profile->getTotalElapsedSeconds(), Logger::INFO);
            }
        });
        $connection->setEventsManager($eventsManager);
    }

    return $connection;
});