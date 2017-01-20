<?php
define('PROJECT_ROOT', realpath(dirname(__FILE__) . "/../"));
date_default_timezone_set('Asia/Shanghai');
if (file_exists(PROJECT_ROOT . "/includes/config.php")) {
    include (PROJECT_ROOT . "/includes/config.php");
} else {
    include (PROJECT_ROOT . "/includes/config.online.php");
}

//  公用类库自动初始化
require_once(PROJECT_ROOT . '/Vendor/Bootstrap/Autoloader.php');
Bootstrap\Autoloader::instance()->init();
require_once(PROJECT_ROOT . "/Import/MallFullImport.php");
require_once(PROJECT_ROOT . "/Import/DealFullImport.php");
require_once(PROJECT_ROOT . "/Import/GlobalDealFullImport.php");
require_once(PROJECT_ROOT . "/Import/PopFullImport.php");
require_once(PROJECT_ROOT . "/Import/GlobalMallFullImport.php");
require_once(PROJECT_ROOT . "/Import/PopMallFullImport.php");
require_once(PROJECT_ROOT . "/Import/GlobalPopMallFullImport.php");
require_once(PROJECT_ROOT . '/util/util_write.php');
require_once(PROJECT_ROOT . '/util/SolrUtil.php');
require_once(PROJECT_ROOT . "/util/DeltaIndexUtil.php");

global $CONFIG;
$redis = new Redis();
$xml_tool = new ImportWrite();
$ip = $CONFIG['server']['redis_ip'];
$port = $CONFIG['server']['redis_port'];
$solr_master = "http://{$CONFIG['server']['solr_master']}/search";

// 类型与处理类映射
$importer_mapping = $CONFIG["importer_mapping"];

if(!isset($argv) || empty($argv) || $argc < 1){
    echo "no product typs are specified. avaliable options:" . json_encode($importer_mapping) . "\n";
    return;
}

foreach ($argv as $type){
    // 运行PHP命令时,php文件名也做为入参
    if(strpos($type, ".php") > 0){
        continue;
    }

    if(!isset($importer_mapping[$type]) || empty($importer_mapping[$type])){
        echo "unknown product type : {$type}\n";
        continue;
    }
    $importerClass = $importer_mapping[$type];
    try{
        echo "processing {$type} detal ...\n";
        $products_array = array();
        if($redis->pconnect($ip, $port, 1)) {
            $queueKey = DeltaIndexUtil::incrAndGetQueueName($redis, $type);
            echo "processing {$queueKey} ...\n";
            $body = "";
            $consumed_message = "";
            if($redis->hLen($queueKey) > 0){
                $importer = new $importerClass();
                $products_array = $redis->hGetAll($queueKey);
                $body .= $xml_tool->gen_xml_del_body($type ,array_keys($products_array));
                $body .= $xml_tool->get_xml_body($importer->read_delta_data(array_values($products_array)));
                echo "generate xml complete ...\r\n";
                $consumed_message .= implode(",", $products_array);
                // 处理过的key,3小时后过期
                $redis->expire($queueKey, 10800);
            }
            echo "fetch completed ...\n";
            DeltaIndexUtil::deltaImport($solr_master, $consumed_message, $body, $type);
            echo "import completed ...\n";
        }
        $redis->close();
        echo sizeof($products_array)." records of {$type} procceed.\n";
    }catch(\Exception $e){
        ImportWrite::write_log("delta_import",$type,"导入失败! ".$e->getMessage(),"/home/logs/delta_import/timer/delta_result.log.".date("Ymd"),"error");
    }
}





