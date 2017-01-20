<?php
define('PROJECT_ROOT', realpath(dirname(__FILE__))."/..");

if (file_exists(PROJECT_ROOT . "/includes/config.php")) {
    include (PROJECT_ROOT . "/includes/config.php");
} else {
    include (PROJECT_ROOT . "/includes/config.online.php");
}

//  公用类库自动初始化
require_once(PROJECT_ROOT . '/Vendor/Bootstrap/Autoloader.php');
Bootstrap\Autoloader::instance()->init();
global $CONFIG;

require_once(PROJECT_ROOT . '/util/JMutil.php');
require_once (PROJECT_ROOT . '/util/util_write.php');

$message = $_POST['message'];
//$message=$_GET['message'];
$msg = json_decode($message, true);
$delta_message_log = "/home/logs/delta_import/delta_message.log.".date("Ymd");
ImportWrite::write_log("delta_message","message_content",$message,$delta_message_log);

try{
    $key = 'default';
    if (!empty($msg)) {
        $category = $msg['category'];
        $ids = $msg['product_id'];
        if($ids == null){
            ImportWrite::write_log("delta_message","exception","失败反馈：product_id为空",$delta_message_log,"error");
            echo("{\"status\":0,\"message\":\"product_id can not be null\"}") ;
            exit();
        }
        $pid = $msg['product_id'];
        if($category == "global"||$category == "pop"||$category == "jumei" ||$category=="promo_cards"){
            $ids = JMutil::retryClient('JumeiProduct_Search_Read_Deals', 'getHashIdByProdcutId', array($ids));
            $ids_array = json_decode($ids, true);
            if($ids == null || empty($ids)){
                $ids = "";
            }else if(is_array($ids_array)){
                $ids = implode(",", $ids_array); // DEAL需要支持一个prodict_id对应多个hash_id
            }
        }
        if ($category == "jumei" ||$category=="promo_cards") {
            $key = 'deal';
        } elseif ($category == "global") {
            $key = 'global_deal';
        } elseif ($category == "pop") {
            $key = 'pop';
        }  elseif ($category == "jumei_mall") {
            $key = 'mall_product';
        } elseif ($category == "global_mall") {
            $key = 'global_mall';
        } elseif ($category == "pop_mall") {
            $key = 'pop_mall';
        } elseif ($category == "global_pop_mall") {
            $key = 'global_pop_mall';
        }else{
            ImportWrite::write_log("delta_message","exception","失败反馈：分类不正确",$delta_message_log,"error");
            echo("{\"status\":0,\"message\":\"have no this category\"}");
            exit();
        }
        $ip = $CONFIG['server']['redis_ip'];
        $port = $CONFIG['server']['redis_port'];
        $redis = new Redis();
        if($redis->pconnect($ip, $port, 1)){
            $flag = "0";
            $pointerKey = "pointer_${key}";
            if($redis->get($pointerKey)){
                $flag = $redis->get($pointerKey);
            }
            $currentQueueKey = "data_${key}_${flag}";
            $succeed = $redis->hSet($currentQueueKey, $pid, $ids);
            $result = ($succeed!==false) ? "{\"status\":1,\"message\":\"success\"}" : "{\"status\":0,\"message\":\"fail\"}";
            ImportWrite::write_log("delta_message",$key,"反馈：{$result} pid:{$pid}　details_ids:{$ids} flag:{$flag}",$delta_message_log);
            echo($result);
            exit();
        }else{
            ImportWrite::write_log("delta_message",$key,"失败反馈：redis连接失败",$delta_message_log,"error");
            echo("{\"status\":0,\"message\":\"redis connect fail.\"}");
            exit();
        }
    }else{
        ImportWrite::write_log("delta_message",$key,"失败反馈：传入格式正确",$delta_message_log);
        echo("{\"status\":0,\"message\":\"error json args\"}");
        exit();
    }
}catch(Exception $e){
    echo("{\"status\":0,\"message\":\"".$e->getMessage()."\"}");
    ImportWrite::write_log("delta_message","exception",$e->getMessage(),$delta_message_log,"error");
    exit();
}




