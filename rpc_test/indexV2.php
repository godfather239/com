<?php 
define('PROJECT_ROOT', realpath(dirname(__FILE__)));
date_default_timezone_set('Asia/Shanghai');

if (file_exists(PROJECT_ROOT . "/includes/config.php")) {
    include (PROJECT_ROOT . "/includes/config.php");
} else {
    include (PROJECT_ROOT . "/includes/config.online.php");
}

//  公用类库自动初始化
require_once(PROJECT_ROOT . '/Vendor/Bootstrap/Autoloader.php');
Bootstrap\Autoloader::instance()->init();
global $CONFIG;
\MNLogger\EXLogger::setUp(array('exception'=>$CONFIG['MNLogger']['exception']));
\MNLogger\TraceLogger::setUp(array('trace'=>$CONFIG['MNLogger']['trace']));
\MNLogger\TraceLogger::instance('trace')->HTTP_SR();

/**
 * 
 * 全量开始，文件全部生成成功才是开始，否则失败。可以启用多线程来实现文件的快速生成
 * 
 * */
require_once(PROJECT_ROOT . "/Import/MallFullImport.php");
require_once(PROJECT_ROOT . "/Import/DealFullImport.php");
require_once(PROJECT_ROOT . "/Import/GlobalDealFullImport.php");
require_once(PROJECT_ROOT . "/Import/PopFullImport.php");
require_once(PROJECT_ROOT . "/Import/GlobalMallFullImport.php");
require_once(PROJECT_ROOT . "/Import/PopMallFullImport.php");
require_once(PROJECT_ROOT . "/Import/GlobalPopMallFullImport.php");

//　类型与处理类映射
$importer_mapping = $CONFIG["importer_mapping"];

try{
    if(!isset($argv) || empty($argv) || $argc < 1){
        echo "no product typs are specified. avaliable options:" . json_encode($importer_mapping) . "\n";
        return;
    }
    $types = $argv;
    if ($argv[0] == "all" || (sizeof($argv) > 1 && $argv[1] == "all")){
        $types = array_keys($importer_mapping);
        echo "full index start ... \r\n";
    }
    foreach ($types as $type){
        if(strpos($type, ".php") > 0){
            continue;
        }

        if(!isset($importer_mapping[$type]) || empty($importer_mapping[$type])){
            echo "unknown product type : {$type}\n";
            continue;
        }
        $importer_name = $importer_mapping[$type];
        echo "{$type} index start ... \r\n";
        echo "{$type} start time " . date("Y-m-d|H:i:s") . "\r\n";

        $full_importer = new $importer_name();
        $full_importer->write_xml($full_importer->read_original_data());

        echo "{$type}  index complete \r\n";
        echo "{$type}  end time " . date("Y-m-d|H:i:s") . "\r\n";
    }
}catch(Exception $ex){
    var_dump("index failed! " . json_encode($ex) . $ex->getMessage() . json_encode($ex->getTrace()));
    exit(2);
}