<?php
define('PROJECT_NAME','AiWuLiu');
define('SITE_PATH',__DIR__.'/');
define('APP_PATH',__DIR__.'/../applications/backend/');
define('COMMON_PATH',__DIR__.'/../applications/common/');
define('APP_NAMESPACE','Backend');
try {
    header("Content-type: text/html; charset=utf-8");
    $config = new Phalcon\Config\Adapter\Php(COMMON_PATH . 'config/config.php');
    $loader = new Phalcon\Loader();
    $loader->registerNamespaces(
        [
            'Oupula\Backend\Controllers' => realpath($config->application->controllersDir),
            'Oupula\Backend\Plugins' => $config->application->pluginsDir,
            'Oupula\Backend\Forms' => $config->application->formsDir,
            'Oupula\Backend\Validation' => realpath($config->application->validationsDir),
            'Oupula\Backend\Library' => realpath($config->application->libraryDir),
            'Oupula\Models' => realpath($config->application->modelsDir),
            'Oupula\Library' => realpath($config->application->commonLibraryDir)
        ]
    );
    $loader->register();
    include(COMMON_PATH.'config/service.php');
    $di->set('config',$config);
    $di->set('router',function() use($di){
        $router = new Phalcon\Mvc\Router;
        $router->notFound(["controller" => "index", "action"     => "index"]);
        return $router;
    });
    $application = new Phalcon\Mvc\Application($di);
    echo $application->handle()->getContent();
} catch (Exception $e) {
    $log_info = sprintf("\r\nfile:%s\r\nline:%s\r\ncode:%s\r\nmessage:%s\r\ntrace:%s\r\n",$e->getFile(),$e->getLine(),$e->getCode(),$e->getMessage(),strip_tags($e->getTraceAsString()));
    $di->get('logger')->log($log_info);
    if(!$config['debug']) {
        header("HTTP/1.1 404 Not Found");
        echo "404 Not Found!";
        exit();
    }else{
        exit("<pre>{$log_info}</pre>");
    }
}