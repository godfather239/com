<?php
define('PROJECT_ROOT', realpath(dirname(__FILE__))."/..");
include (PROJECT_ROOT . "/includes/config.php");
//  公用类库自动初始化
require_once(PROJECT_ROOT . '/Vendor/Bootstrap/Autoloader.php');
Bootstrap\Autoloader::instance()->init();
require_once(PROJECT_ROOT . '/util/JMutil.php');

try{
    $types = $_GET['types'];
    $types = explode(",", $types);
    if(!isset($types) || empty($types) || sizeof($types) < 1){
        return;
    }

    foreach($types as $type){
        try{
            $reslut = exec("/home/shell/single_index.sh '{$type}'",$res,$rc);
            echo " records of {$type} procceed.\n";
            if($rc==0){
                var_dump($res);
            }else{
                var_dump("执行失败---".$type);
                var_dump($res);
            }
        }catch(\Exception $e){
            echo "error:".$e->getMessage()."\n";
            file_put_contents("/home/logs/".date("Ymd").".log", date("Y-m-d|H:i:s")." {$type} 导入失败! ".$e->getMessage()." \n", FILE_APPEND);
        }
    }

}catch(\Exception $e){
    echo "error:".$e->getMessage()."\n";
    file_put_contents("/home/logs/".date("Ymd").".log", date("Y-m-d|H:i:s")."  导入失败! ".$e->getMessage()." \n", FILE_APPEND);
}
