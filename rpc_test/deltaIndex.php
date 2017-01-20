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

require_once(PROJECT_ROOT . "/Import/MallFullImport.php");
require_once(PROJECT_ROOT . "/Import/DealFullImport.php");
require_once(PROJECT_ROOT . "/Import/GlobalDealFullImport.php");
require_once(PROJECT_ROOT . "/Import/PopFullImport.php");
require_once(PROJECT_ROOT . "/Import/GlobalMallFullImport.php");
require_once(PROJECT_ROOT . "/Import/PopMallFullImport.php");
require_once(PROJECT_ROOT . "/Import/GlobalPopMallFullImport.php");
require_once(PROJECT_ROOT . '/util/util_write.php');
require_once(PROJECT_ROOT . '/util/SolrUtil.php');

global $CONFIG;
$ip = $CONFIG['server']['redis_ip'];
$port = $CONFIG['server']['redis_port'];
$solr_master="http://{$CONFIG['server']['solr_master']}/search";
$redis = new Redis();
$xml_tool=new ImportWrite();
$body="";
$consumed_message="";
if($redis->pconnect($ip, $port, 1)){
    $flag=$redis->get("flag");
    $redis->set("flag",date('ymdHi'));
    if($redis->hLen("global_deal".$flag)>0){
        $global_deal=new GlobalDealFullImport();
        $global_deal_array = $redis->hGetAll("global_deal".$flag);
        $body.=$xml_tool->gen_xml_del_body("global_deal",array_keys($global_deal_array));
        $body.=$xml_tool->get_xml_body($global_deal->field_transform($global_deal->read_delta_data(array_values($global_deal_array))));
        $consumed_message .= implode(",",$global_deal_array);
    }
    if($redis->hLen("global_mall".$flag)>0){
        $global_mall=new GlobalMallFullImport();
        $global_mall_array=$redis->hGetAll("global_mall".$flag);
        $body.=$xml_tool->gen_xml_del_body("global_mall",array_keys($global_mall_array));
        $body.=$xml_tool->get_xml_body($global_mall->field_transform($global_mall->read_delta_data(array_values($global_mall_array))));
        $consumed_message .= implode(",",$global_mall_array);
    }
    if($redis->hLen("mall_product".$flag)>0){
        $mall_product=new MallFullImport();
        $mall_product_array=$redis->hGetAll("mall_product".$flag);
        $body.=$xml_tool->gen_xml_del_body("mall_product",array_keys($mall_product_array));
        $body.=$xml_tool->get_xml_body($mall_product->field_transform($mall_product->read_delta_data(array_values($mall_product_array))));
        $consumed_message .= implode(",",$mall_product_array);
    }
    if($redis->hLen("deal".$flag)>0){
        $deal=new DealFullImport();
        $deal_array=$redis->hGetAll("deal".$flag);
        $body.=$xml_tool->gen_xml_del_body("deal",array_keys($deal_array));
        $body.=$xml_tool->get_xml_body($deal->field_transform($deal->read_delta_data($deal_array)));
        $consumed_message .= implode(",",$deal_array);
    }
    if($redis->hLen("pop".$flag)>0){
        $pop=new PopFullImport();
        $pop_array=$redis->hGetAll("pop".$flag);
        $body.=$xml_tool->gen_xml_del_body("pop",array_keys($pop_array));
        $body.=$xml_tool->get_xml_body($pop->field_transform($pop->read_delta_data(array_values($pop_array))));
        $consumed_message .= implode(",",$pop_array);
    }
    if($redis->hLen("pop_mall".$flag)>0){
        $pop_mall=new PopMallFullImport();
        $pop_mall_array=$redis->hGetAll("pop_mall".$flag);
        $body.=$xml_tool->gen_xml_del_body("pop_mall",array_keys($pop_mall_array));
        $body.=$xml_tool->get_xml_body($pop_mall->field_transform($pop_mall->read_delta_data(array_values($pop_mall_array))));
        $consumed_message .= implode(",",$pop_mall_array);
    }
    if($redis->hLen("global_pop_mall".$flag)>0){
        $global_pop_mall=new GlobalPopMallFullImport();
        $global_pop_mall_array=$redis->hGetAll("global_pop_mall".$flag);
        $body.=$xml_tool->gen_xml_del_body("global_pop_mall",array_keys($global_pop_mall_array));
        $body.=$xml_tool->get_xml_body($pop_mall->field_transform($pop_mall->read_delta_data(array_values($global_pop_mall_array))));
        $consumed_message .= implode(",",$global_pop_mall_array);
    }

    file_put_contents("/home/logs/delta_import/datalog.txt", date("Y-m-d|H:i:s")." consumed: {$consumed_message} \n",FILE_APPEND);
    file_put_contents("/home/logs/delta_import/datalog.txt", date("Y-m-d|H:i:s")." result: {$body} \n",FILE_APPEND);

//    echo($body);
    if($body!=""){
        $xml=$xml_tool->gen_xml($body);
        $solr = new SolrUtil($solr_master, 'search_jumei_com');
        $solrData=$solr->update($xml);
        if (empty($solrData) == false) {
            $data = json_decode($solrData, true);
            if (isset($data['responseHeader']['status']) == true && $data['responseHeader']['status'] == 0) {
                $delta_keys=array('deal'.$flag,'global_deal'.$flag,'pop'.$flag,'mall_product'.$flag,'global_mall'.$flag,'pop_mall'.$flag);
                $redis->del($delta_keys);
                file_put_contents("/home/logs/delta_import/timer/".date("Ymd").".log",date("Y-m-d|H:i:s")." {$flag}增量导入成功\n",FILE_APPEND);
            } else {
                file_put_contents("/home/logs/delta_import/timer/".date("Ymd").".log",date("Y-m-d|H:i:s")." {$flag}增量导入失败\n",FILE_APPEND);
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