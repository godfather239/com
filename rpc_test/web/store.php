<?php
define('PROJECT_ROOT', realpath(dirname(__FILE__))."/..");

if (file_exists(PROJECT_ROOT . "/includes/config.php"))
{
    include (PROJECT_ROOT . "/includes/config.php");
} else
{
    include (PROJECT_ROOT . "/includes/config.online.php");
}

//  公用类库自动初始化
require_once(PROJECT_ROOT . '/Vendor/Bootstrap/Autoloader.php');
Bootstrap\Autoloader::instance()->init();
global $CONFIG;

require_once(PROJECT_ROOT . '/util/JMutil.php');
$message=file_get_contents("php://input");
$msg = json_decode($message, true);
file_put_contents("/home/logs/delta_import/".date("Ymd").".log",date("Y-m-d|H:i:s")."店铺增量入参：".$message."\n",FILE_APPEND);
try{
    $key = 'store_ids';
    if (!empty($msg)) {
        if($msg["table"]=="jumei_flagship_store"){
            $store_id=0;
            $store_level=0;
            foreach($msg["fields"] as $v){
                if($v["columnName"]=="store_level"){
                    $store_level=$v["value"];
                }
                if($v["columnName"]=="store_id"){
                    $store_id=$v["value"];
                }
            }
            if($store_id==0){
                file_put_contents("/home/logs/delta_import/".date("Ymd").".log",date("Y-m-d|H:i:s")."：店铺增量失败反馈：store_id为空\n",FILE_APPEND);
                echo("{\"status\":0,\"message\":\"store_ids can not be null\"}") ;
                exit();
            }

            $ip = $CONFIG['server']['redis_ip'];
            $port = $CONFIG['server']['redis_port'];
            $redis = new Redis();
            if($redis->pconnect($ip, $port, 1)){
                if($store_level !=0){
                    $redis->sAdd($key,$store_id);
                }
                $result="{\"status\":1,\"message\":\"success\"}";
                file_put_contents("/home/logs/delta_import/".date("Ymd").".log",date("Y-m-d|H:i:s")."店铺增量反馈：{$result}\n",FILE_APPEND);
                echo($result);
                exit();
            }else{
                file_put_contents("/home/logs/delta_import/".date("Ymd").".log",date("Y-m-d|H:i:s")."店铺增量失败反馈：redis连接失败\n",FILE_APPEND);
                echo("{\"status\":0,\"message\":\"redis connect fail.\"}");
                exit();
            }
        }
    }else{
        file_put_contents("/home/logs/delta_import/".date("Ymd").".log",date("Y-m-d|H:i:s")."店铺增量失败反馈：传入格式正确\n",FILE_APPEND);
        echo("{\"status\":0,\"message\":\"error json args\"}");
        exit();
    }
}catch(Exception $e){
    echo("{\"status\":0,\"message\":\"".$e->getMessage()."\"}");
    file_put_contents("/home/logs/delta_import/".date("Ymd").".log",date("Y-m-d|H:i:s")."店铺增量失败异常：".$e->getMessage()."\n",FILE_APPEND);
    exit();
}




