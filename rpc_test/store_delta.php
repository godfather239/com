<?php
define('PROJECT_ROOT', realpath(dirname(__FILE__)));
date_default_timezone_set('Asia/Shanghai');

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

require_once(PROJECT_ROOT . '/util/util_write.php');
require_once(PROJECT_ROOT . '/util/SolrUtil.php');
require_once(PROJECT_ROOT . "/Import/StoreFullImport.php");

global $CONFIG;
$ip = $CONFIG['server']['redis_ip'];
$port = $CONFIG['server']['redis_port'];
$solr_master="http://{$CONFIG['server']['solr_master']}/search";
$redis = new Redis();
$xml_tool=new ImportWrite();
$body="";
if($redis->pconnect($ip, $port, 1)){
    $Store=new StoreFullImport();
    $ids=array();
    while($store_id=$redis->sPop("store_ids")) {
        array_push($ids, $store_id);
    }
    $body.=$xml_tool->gen_xml_del_store_body($ids);
    $body.=$xml_tool->get_xml_body($Store->field_transform($Store->read_delta_data($ids)));

    $ids_str=json_encode($ids);
    file_put_contents("/home/logs/delta_import/datalog.txt", date("Y-m-d|H:i:s")." consumed: {$ids_str} \n",FILE_APPEND);
    file_put_contents("/home/logs/delta_import/datalog.txt", date("Y-m-d|H:i:s")." result: {$body} \n",FILE_APPEND);

    if($body!=""){
        $xml=$xml_tool->gen_xml($body);
        $solr = new SolrUtil($solr_master, 'store_jumei_com');
        $solrData=$solr->update($xml);
        if (empty($solrData) == false) {
            $data = json_decode($solrData, true);
            if (isset($data['responseHeader']['status']) == true && $data['responseHeader']['status'] == 0) {

                file_put_contents("/home/logs/delta_import/timer/".date("Ymd").".log",date("Y-m-d|H:i:s")." 店铺{$ids}增量导入成功\n",FILE_APPEND);
            } else {
                file_put_contents("/home/logs/delta_import/timer/".date("Ymd").".log",date("Y-m-d|H:i:s")."店铺{$ids}增量导入失败\n",FILE_APPEND);
                file_put_contents("/home/logs/delta_import/timer/".date("Ymd").".log",date("Y-m-d|H:i:s")."调用失败：".$solrData."\n",FILE_APPEND);
                throw new Exception('xml导入失败:' .$solrData);
            }
        } else {
            file_put_contents("/home/logs/delta_import/log.txt",date("Y-m-d|H:i:s")."solr无响应\n",FILE_APPEND);
            file_put_contents("/home/logs/delta_import/timer/".date("Ymd").".log",date("Y-m-d|H:i:s")."solr无响应\n",FILE_APPEND);
            throw new Exception('solr无响应');
        }
    }

}