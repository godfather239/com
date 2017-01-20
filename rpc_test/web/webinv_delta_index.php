<?php
define('PROJECT_ROOT', realpath(dirname(__FILE__))."/..");
include (PROJECT_ROOT . "/includes/config.php");
//  公用类库自动初始化
require_once(PROJECT_ROOT . '/Vendor/Bootstrap/Autoloader.php');
Bootstrap\Autoloader::instance()->init();
require_once(PROJECT_ROOT . '/util/JMutil.php');
require_once(PROJECT_ROOT . '/util/ProdDeltaIndexExecutor.php');

$types = $_GET['types'];
$debug = $_GET['debug'];

$types = explode(",", $types);
ProdDeltaIndexExecutor::execDeltaIndex($types, $debug);


