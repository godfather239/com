<?php
require_once (PROJECT_ROOT . '/util/util_write.php');

class DeltaIndexUtil {

    public static function addProductToDeltaQueue($redis, $products){
        $date = date("Ymd");
        $product_ids  = "";
        foreach($products as $product){
            $docType = $product["doc_type"];
            $pid = $product["product_id"];

            $detailIds = $pid;
            // 如果是deal,则需要获取其对应hash_id
            if($docType == 'deal' || $docType == 'global_deal' ||  $docType == 'pop'){
                $detailIds = DeltaIndexUtil::getHashIdByProductId($pid);
                if(empty($detailIds)){
                    file_put_contents("/home/logs/delta_import/promotion_delta_{$date}.log", date("Y-m-d|H:i:s")." no hash_id return for pdela {$pid} \n",FILE_APPEND);
                    continue;
                }
            }
            $pointerKey = "pointer_{$docType}";
            $flag = $redis->get($pointerKey);
            if(!$flag){
                $flag = "0";
            }
            $product_ids .= $docType.":".$pid . ",";
            $queueKey = "data_{$docType}_{$flag}";
            $redis->hSetNx($queueKey, $pid, $detailIds);
        }
        file_put_contents("/home/logs/delta_import/promotion_delta_{$date}.log", date("Y-m-d|H:i:s")." promotion consumed, in-queue: {$product_ids} \n",FILE_APPEND);
    }
    
    public static function getHashIdByProductId($product_ids){
        $ids = JMutil::retryClient('JumeiProduct_Search_Read_Deals', 'getHashIdByProdcutId', array($product_ids));
        $ids_array = json_decode($ids, true);
        if($ids == null || empty($ids)){
            $ids = "";
        }else if(is_array($ids_array)){
            $ids = implode(",", $ids_array);
        }
        return $ids;
    }
    
    public static function deltaImport($solr_master, $consumed_message, $body, $type){
        $xml_tool = new ImportWrite();
        $date = date("Ymd");
        echo "consumed: {$consumed_message} \n";
        $delta_data_log = "/home/logs/delta_import/datalog.log.{$date}";
        $delta_result_log = "/home/logs/delta_import/timer/delta_result.log.{$date}";
        ImportWrite::write_log("delta_ids",$type,$consumed_message,$delta_data_log);
        ImportWrite::write_log("delta_detail",$type,$body,$delta_data_log);

        if($body != ""){
            $xml = $xml_tool->gen_xml($body);
            $solr = new SolrUtil($solr_master, 'search_jumei_com');
            $solrData=$solr->update($xml);
            if (empty($solrData) == false) {
                $data = json_decode($solrData, true);
                if (isset($data['responseHeader']['status']) == true && $data['responseHeader']['status'] == 0) {
                    // $delta_keys=array('deal'.$flag,'global_deal'.$flag,'pop'.$flag,'mall_product'.$flag,'global_mall'.$flag,'pop_mall'.$flag);
                    // $redis->del($delta_keys);
                    ImportWrite::write_log("delta_import",$type,"增量导入成功",$delta_result_log);
                } else {
                    ImportWrite::write_log("delta_import",$type,"增量导入失败",$delta_result_log,"error");
                    ImportWrite::write_log("delta_import",$type,"调用失败：".$solrData,$delta_result_log,"error");
                    throw new Exception('xml导入失败:' . $solrData);
                }
            } else {
                ImportWrite::write_log("delta_import",$type,"solr无响应：".$solrData,$delta_result_log,"error");
                throw new Exception('solr无响应');
            }
        }
    }
    
    public static function incrAndGetQueueName($redis, $type){
        $flag = $redis->get("pointer_{$type}");
        if (!isset($flag) || empty($flag)) {
            $flag = "0";
        }
        $new_flag = date('ymdHi');
        $redis->set("pointer_{$type}", $new_flag);
        
        $queueKey = "data_{$type}_" . $flag;
        $date = date("Ymd");
        ImportWrite::write_log("delta_pointer",$type,"queueKey:{$queueKey}","/home/logs/delta_import/datalog.log.{$date}");
        return $queueKey;
    }

}