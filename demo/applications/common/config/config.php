<?php
return array(
    'sqlCount' => 0,
    'debug'=>true,
    'rewrite' => true,
    'database'=>array(
        'adapter' => 'Mysql',
        'host' => 'localhost',
        'username' => 'root',
        'password' => 'root',
        'dbname' => 'demo',
        'charset' => 'utf8',
        'prefix' => '',
        'field_cache' => false,
        'debug' => true,
        'db_like_fields'    =>  '',
    ),
    // 'cache' => array(
    //     'adapter' => 'Redis',//Redis,Memcache,File
    //     'prefix' => 'wbd_',
    //     'lifetime' => 86400,
    //     'host' => '192.168.100.88',
    //     'port' => 6379,
    //     'persistent' => true
    // ),
    'cache' => array(
        'adapter' => 'Redis',//Redis,Memcache,File
        'prefix' => 'wbd_',     
        'lifetime' => 86400,        
        'host' => '127.0.0.1',
        'port' => 6379,
        'persistent' => true
    ),
    'application'=>array(
        'controllersDir' => APP_PATH . 'controllers',
        'validationsDir' => APP_PATH . 'validations',
        'modelsDir' => APP_PATH . '../common/models',
        'viewsCacheDir' => APP_PATH . 'cache/views/',
        'dataCacheDir' => APP_PATH .'cache/data/',
        'viewsDir' => APP_PATH . 'views',
        'pluginsDir' => APP_PATH . 'plugins',
        'formsDir' => APP_PATH . 'forms',
        'libraryDir' => APP_PATH . 'library',
        'commonLibraryDir' => APP_PATH . '../common/library',
        'loggerDir' => APP_PATH .'log',
        'baseUri' => '/',
        'frontDir' => SITE_PATH . '../',
        'uploadDir' => SITE_PATH . '../wwwroot/',
        'backupDir' => SITE_PATH .'../backup/database/'
    )
);