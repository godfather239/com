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
$message=$_POST['message'];
$rule = json_decode($message, true);
file_put_contents("/home/logs/promotion_change_message/".date("Ymd").".log",date("Y-m-d|H:i:s")."入参：".$message."\n",FILE_APPEND);
$succes_result = "{\"status\":1,\"message\":\"success\"}";
try{
    if(empty($rule) || empty($rule["ruleId"])){
        echo("{\"status\":0,\"message\":\"empty rule\"}");
        exit();
    }
    // 全场促销规则,搜索不处理
    if(isset($rule["isWholeBooth"]) && $rule["isWholeBooth"] == 1){
        return $succes_result;
    }

    $ip = $CONFIG['server']['redis_ip'];
    $port = $CONFIG['server']['redis_port'];
    $redis = new Redis();
    if($redis->pconnect($ip, $port, 1)){
        $ret = $redis->sAdd("promotion_change_queue", $rule["ruleId"]);
        if($ret > 0) {
            echo($succes_result);
        }else {
            echo("{\"status\":0,\"message\":\"redis access failed!\"}");
        }
    }

}catch(Exception $e){
    echo("{\"status\":0,\"message\":\"".$e->getMessage()."\"}");
    file_put_contents("/home/logs/promotion_change_message/".date("Ymd").".log",date("Y-m-d|H:i:s")."失败异常：".$e->getMessage()."\n",FILE_APPEND);
    exit();
}
//
//$key = 'default';
//if (!empty($msg)) {
//    $category = $msg['category'];
//    $ids = $msg['product_id'];
//    if($ids==null){
//        file_put_contents("/home/logs/delta_import/".date("Ymd").".log",date("Y-m-d|H:i:s")."：失败反馈：product_id为空\n",FILE_APPEND);
//        echo("{\"status\":0,\"message\":\"product_id can not be null\"}") ;
//        exit();
//    }
//    $pid=$msg['product_id'];
//    if($category == "global"||$category == "pop"||$category == "jumei" ||$category=="promo_cards"){
//        $ids = JMutil::retryClient('JumeiProduct_Search_Read_Deals', 'getHashIdByProdcutId', array($ids));
//        $ids_array = json_decode($ids, true);
//        if($ids == null || empty($ids)){
//            $ids = "";
//        }else if(is_array($ids_array)){
//            // DEAL需要支持一个prodict_id对应多个hash_id
//            $ids = implode(",", $ids_array);
//        }
//    }
//    if ($category == "jumei" ||$category=="promo_cards") {
//        $key='deal';
//    } elseif ($category == "global") {
//        $key='global_deal';
//    } elseif ($category == "pop") {
//        $key='pop';
//    }  elseif ($category == "jumei_mall") {
//        $key='mall_product';
//    } elseif ($category == "global_mall") {
//        $key='global_mall';
//    } elseif ($category == "pop_mall") {
//        $key='pop_mall';
//    }else{
//        file_put_contents("/home/logs/delta_import/".date("Ymd").".log",date("Y-m-d|H:i:s")."失败反馈：分类不正确\n",FILE_APPEND);
//        echo("{\"status\":0,\"message\":\"have no this category\"}");
//        exit();
//    }
//    $ip = $CONFIG['server']['redis_ip'];
//    $port = $CONFIG['server']['redis_port'];
//    $redis = new Redis();
//    if($redis->pconnect($ip, $port, 1)){
//        $flag="0";
//        if($redis->get("flag")){
//            $flag=$redis->get("flag");
//        }
//
//        $redis->hSetNx($key.$flag,$pid,$ids);
//        $result="{\"status\":1,\"message\":\"success\"}";
//        file_put_contents("/home/logs/delta_import/".date("Ymd").".log",date("Y-m-d|H:i:s")."反馈：{$result}\n",FILE_APPEND);
//        echo($result);
//        exit();
//    }else{
//        file_put_contents("/home/logs/delta_import/".date("Ymd").".log",date("Y-m-d|H:i:s")."失败反馈：redis连接失败\n",FILE_APPEND);
//        echo("{\"status\":0,\"message\":\"redis connect fail.\"}");
//        exit();
//    }
//
//}else{
//    file_put_contents("/home/logs/delta_import/".date("Ymd").".log",date("Y-m-d|H:i:s")."失败反馈：传入格式正确\n",FILE_APPEND);
//    echo("{\"status\":0,\"message\":\"error json args\"}");
//    exit();
//}
//}catch(Exception $e){
//    echo("{\"status\":0,\"message\":\"".$e->getMessage()."\"}");
//    file_put_contents("/home/logs/delta_import/".date("Ymd").".log",date("Y-m-d|H:i:s")."失败异常：".$e->getMessage()."\n",FILE_APPEND);
//    exit();
//}
//
//
//
//
