<?php
return array(
    'debug' => true,
    'profiler' => true,
    'baseUrl' => '/phalcon/',
    'dbMaster' =>
        array(
            'adapter' => 'Mysql',
            'host' => '172.16.8.113',
            'port' => '3306',
            'username' => 'root',
            'password' => 'root',
            'dbname' => 'phpcms',
            'prefix' => 'wj_',
            'charset' => 'UTF8',
        ),
    'dbSlave' =>
        array(
            'adapter' => 'Mysql',
            'host' => '172.16.8.113',
            'port' => '3306',
            'username' => 'root',
            'password' => 'root',
            'dbname' => 'phpcms',
            'prefix' => 'wj_',
            'charset' => 'UTF8',
        ),
    'cache' =>
        array(
            'lifetime' => '86400',
            'adapter' => 'File',
            'host' => '172.16.8.113',
            'port' => 6379,
            'persistent' => true,
//            'statsKey' => 'WuJie_',
            'cacheDir' => CACHE_PATH . '/data/',
        ),
    'queue' =>
        array(
            'host' => '127.0.0.1',
            'port' => 6379,
//            'auth'=>'',
            'persistent' => true,
        ),
    'logger' =>
        array(
            'enabled' => true,
            'path' => DATA_PATH . '/logs/',
            'format' => '[%date%][%type%] %message%',
        ),
    'upload' =>
        array(
            'path' => DATA_PATH . '/upload/'
        ),
    'view' =>
        array(
            'viewsDir' => APP_PATH . '/views/',
        ),
    'session' =>
        array(
            'adapter' => 'Files',
            //'path' => "tcp://127.0.0.1:6379",
            'uniqueId' => 'WuJie_',
        ),
    'assets' =>
        array(
            'local' => 'assets/',
            'remote' => false,
            'lifetime' => 0,
        ),
    'metadata' =>
        array(
            'adapter' => 'Files',
            'metaDataDir' => CACHE_PATH . '/metadata/',
            'options' => array(
                'lifetime' => 1800, // 30 minutes
                'prefix' => '',
                'metaDataDir' => CACHE_PATH . '/metadata/',
            ),
        ),
    'annotations' =>
        array(
            'adapter' => 'Files',
            'annotationsDir' => CACHE_PATH . '/annotations/',
        ),
    'smtp' =>
        array(
            'driver' => 'smtp',
            'host' => 'smtp.263.net',
            'port' => 25,
            'username' => 'xiaosongzhu@caijing.com.cn',
            'password' => 'test',
            'from'       =>  'xiaosongzhu@caijing.com.cn'
        ),
    'languages' =>
        array(
            'cacheDir' => CACHE_PATH . '/languages/',
        ),
);

