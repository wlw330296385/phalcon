<?php
return array(
    'sqlCount' => 0,
    'debug'=>true,
    'rewrite' => true,
    'database'=>array(
        'adapter' => 'Mysql',
        'host' => 'localhost',
        'username' => 'wbd',
        'password' => '123456',
        'dbname' => 'wbd',
        'charset' => 'utf8',
        'prefix' => 'wbd_',
        'field_cache' => true,
        'debug' => false,
        'db_like_fields'    =>  '',
    ),
    'cache' => array(
        'adapter' => 'Redis',//Redis,Memcache,File
        'lifetime' => 86400,
        'host' => 'localhost',
        'port' => 6379,
        'persistent' => false
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