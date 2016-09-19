<?php
use Phalcon\escaper;
use Phalcon\Db\Adapter\Pdo\Mysql as DB;
use Phalcon\Db\Profiler as DbProfiler;
use Phalcon\Logger;
use Phalcon\Logger\Formatter\Line as LineFormatter;
use Phalcon\Logger\Adapter\File as FileAdapter;
use Phalcon\Mvc\Model\Transaction\Manager as TransactionManager;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\DI\FactoryDefault\CLI as CliDI;
use Phalcon\CLI\Console as ConsoleApp;

define('SITE_PATH', __DIR__ . '/');
define('APP_PATH', __DIR__ . '/../applications/console/');
define('COMMON_PATH', __DIR__ . '/../applications/common/');
define('APP_NAMESPACE', 'Console');
$config = new Phalcon\Config\Adapter\Php(COMMON_PATH . 'config/config.php');
$loader = new Phalcon\Loader();
$loader->registerNamespaces(
    [
        'Oupula\Console\Plugins' => $config->application->pluginsDir,
        'Oupula\Console\Library' => realpath($config->application->libraryDir),
        'Oupula\Models' => realpath($config->application->modelsDir),
        'Oupula\Library' => realpath($config->application->commonLibraryDir)
    ]
);
$loader->registerDirs([APP_PATH . 'tasks']);
$loader->register();
$di = new CliDI();
$di->set('config', $config);
$di->set('modelsMetadata', 'Phalcon\Mvc\Model\Metadata\Memory');
$di->set('modelsManager', 'Phalcon\Mvc\Model\Manager');
$di->set('filter', 'Phalcon\Filter');
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
    $cacheConfig = $config->cache;
    $cacheConfig['prefix'] = '_cache_';
    $cacheData = new Phalcon\Cache\Frontend\Data(['lifetime' => $cacheConfig['lifetime']]);
    if ($cacheConfig['adapter'] == 'Redis') {
        $cache = new Phalcon\Cache\Backend\Redis($cacheData, $cacheConfig);
    } else if ($cacheConfig['adapter'] == 'Memcache') {
        $cache = new Phalcon\Cache\Backend\Memcache($cacheData, $cacheConfig);
    } else {
        if (!is_dir($config->application->dataCacheDir . '_fields')) {
            mkdir($config->application->dataCacheDir . '_fields', 0777, true);
        }
        $cache = new Phalcon\Cache\Backend\File($cacheData, ['cacheDir' => $config->application->dataCacheDir]);
    }
    return $cache;
});
$di->set('db', function () use ($config) {
    $connection = new DB([
        "host" => $config->database->host,
        "username" => $config->database->username,
        "password" => $config->database->password,
        "dbname" => $config->database->dbname,
        "charset" => $config->database->charset,
    ]);
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
$console = new ConsoleApp();
$console->setDI($di);
$arguments = [];
$params = [];


foreach ($argv as $k => $arg) {
    if ($k == 1) {
        $arguments['task'] = $arg;
    } elseif ($k == 2) {
        $arguments['action'] = $arg;
    } elseif ($k >= 3) {
        $params[] = $arg;
    }
}
if (count($params) > 0) {
    $arguments['params'] = $params;
}
define('CURRENT_TASK', (isset($argv[1]) ? $argv[1] : null));
define('CURRENT_ACTION', (isset($argv[2]) ? $argv[2] : null));


try {
    $console->handle($arguments);
} catch (Exception $e) {
    $di->get('logger')->log($e->getMessage());
    if (!$config['debug']) {
        exit(255);
    } else {
        echo $e->getMessage() . '<br/>';
        echo nl2br($e->getTraceAsString());
        exit(255);
    }
}