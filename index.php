<?php
date_default_timezone_set ('Asia/Shanghai');
//error_reporting (E_ALL & ~E_NOTICE);
error_reporting(E_ALL | E_STRICT);
ini_set ('display_errors', 'On');

if(!extension_loaded ('phalcon'))
    exit('Phalcon framework extension is not installed');

if(!extension_loaded ('pdo_mysql'))
    exit('PDO_MYSQL extension is not installed');

if(version_compare (phpversion (), 5.4, '<')) {
    printf ('PHP %s is required, you have %s.', 5.4, phpversion ());
    exit(1);
}

define('ROOT_PATH', str_replace ('\\', '/', dirname (__FILE__)));
require_once ROOT_PATH . '/app/functions/core.php';
require_once ROOT_PATH . '/app/config/defined.php';
require_once APP_PATH . "/engine/Application.php";
$application = new Engine\Application();
$application->run ();
echo $application->getOutput ();
