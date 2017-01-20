<?php

class Yqt{
    /**
     * 通过类型分页获取一起团hash_id.
     *
     * @param $flag integer  查询条件中最小deal_id.
     * @param string  $type  查询类型.
     *
     * @return string
     */
    public static function getYQTByTypeLimit($flag,$type){
        $hash_ids =JMutil::retryClient('JumeiProduct_Search_Read_Deals', 'getYQTByTypeLimit', array($flag,50,$type));
        ImportWrite::log_info("type:YQT_{$type}, flag:{$flag}, ids:{$hash_ids}");
        return $hash_ids;
    }

    /**
     * 获取一起团详情
     *
     * @param $fp resource 文件.
     * @param $type  string   类型.
     * @param $obj  mixed    对象引用.
     *
     * @return string
     */
    public static function getYQTDeal($fp, $type, $obj){
        require_once (PROJECT_ROOT . '/util/util_write.php');
        $flag=0;
        if($type == "pop_deal" || $type=="global_pop" || $type=="global_deal"){
            while($flag!=-1){
                $hash_ids =Yqt::getYQTByTypeLimit($flag,$type);
                if($hash_ids=="[{\"minId\":".$flag."},[]]"){
                    $flag = -1;
                }else{
                    $deal_detail = array();
                    $hash_ids_arrays = json_decode($hash_ids,true);
                    if(!empty($hash_ids_arrays)) {
                        $flag = $hash_ids_arrays[0]['minId'];
                        $ids = $hash_ids_arrays[1];
                        $result = $obj->getDetails($ids);
                        $deal_detail = $obj->detail2Product($deal_detail,$result,$ids);
                        $xml = ImportWrite::get_xml_body($deal_detail);
                        if (!empty($xml) && isset($fp)) {
                            fwrite($fp, $xml);
                        }
                    }
                }
            }
            return $fp;
        }
        if($type=="jumei_deal"){
            $deal_detail = array();
            while ($flag != -1) {
                $deals_str = Yqt::getYQTByTypeLimit($flag,$type);
                if ($deals_str == "[{\"minId\":".$flag."},[]]") {
                    $flag = -1;
                } else {
                    $deals_data = json_decode($deals_str, true);
                    if (!empty($deals_data)) {
                        $flag = $deals_data[0]['minId'];
                        $ids = $deals_data[1];
                        $result =$obj->getDetails($ids);
                        $deal_detail=$obj->detail2ProductYqt($deal_detail,$result,$ids);
                    }
                }
            }
            return $deal_detail;
        }

    }
}
