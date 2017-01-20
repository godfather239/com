<?php
require_once (PROJECT_ROOT . "/Import/load_source_data.php");
include(PROJECT_ROOT . "/util/CRedis.php");

class StoreFullImport implements LoadSourceData
{
    public function read_original_data()
    {
        require PROJECT_ROOT . '/Vendor/PHPClient/JMTextRpcClient.php';
        require PROJECT_ROOT . '/Vendor/PHPClient/JMRpcClient.php';
        require PROJECT_ROOT . '/Vendor/PHPClient/Text.php';
        $store_full = array();
        require_once (PROJECT_ROOT . '/util/JMutil.php');
        $flag = 1;
        $store_detail = array(
            "init value"
        );
        while (sizeof($store_detail) != 0) {
            for ($i = 0; $i < 3; $i ++) {
                $store_detail = JMutil::getThriftClient('Search_PopStore', 'getStoreListByPage', array(
                    $flag
                ));
                $shopid = array_keys($store_detail);
                if (sizeof($store_detail) != 0) {
                    if ($store_detail != null) {
                        $store_array = array_chunk($shopid, 50);

                        // 填充店铺收藏数据
                        foreach ($store_array as $ids) {
                            $favCounts = \PHPClient\Text::inst('UserInfo')->setClass('Favourite')->getShopFavCountByShopIds($ids, true);
                            if ($favCounts['errorcode'] == 0) {
                                $store_count = $favCounts["data"];
                                foreach ($store_count as $k => $v) {
                                    $store_detail[$k]['fav_count'] = $v;
                                }
                                //$store_full = $store_full + $store_detail;

                            } else {
                                // throw new Exception('调用获取收藏店铺接口失败，失败代码:' . $favCounts[0]); 记录异常,不抛出
                                error_log("时间" . date("Y-m-d|H:i:s") . "调用获取收藏店铺接口失败，失败代码:" . $favCounts[0], 3, "/home/logs/data_import/store_error.log");
                            }
                        }

                        // 填充店铺品牌信息
                        foreach($store_detail as $store_id => $store_info){
                            $brandStr = $store_info["brand"];
                            if(!isset($brandStr) || empty($brandStr)){
                                continue;
                            }
                            $brandIdArray = json_decode($brandStr);
                            foreach($brandIdArray as $brand_id){
                                $brandInfo = $this->getBrandInfo($brand_id);
                                $store_detail[$store_id]["brand_details"][] = $brandInfo;
                            }
                        }
                        $store_full = $store_full + $store_detail;

                        $flag ++;
                        break;
                    } else {
                        error_log("时间" . date("Y-m-d|H:i:s") . "调用旗舰店服务服务失败，重试" . $i . "次：flag:" . $flag . " result:" . var_dump($store_detail), 3, "/home/logs/data_import/store_error.log");
                        if ($i >= 2) {
                            throw new Exception('调用旗舰店服务3次无反馈，可能引起索引异常，放弃索引建立');
                        }
                    }
                }
            }
        }
       // var_dump($store_full);
        return $store_full;
    }

    /**
     * 获取品牌信息(品牌名称,在售商品数量)
     * @param $brandId
     * @return array|mixed|null
     * @throws Exception
     */
    public function getBrandInfo($brandId){
        global $CONFIG;
        $solr_master = $CONFIG["server"]["solr_master"];

        if(!isset($brandId) || empty($brandId)){
            return null;
        }

        // 先从缓存中获取
        $redis = \CRedis::cache('search');
        $brandInfo = $redis->get("search_indexing_brand_${brandId}");
        if(isset($brandInfo) && !empty($brandInfo)){
            return json_decode($brandInfo, 1);
        }

        // 缓存没有,则从solr中获取品牌信息
        $brandInfo = array("product_count" => 0);
        $url = "http://{$solr_master}/search/search_jumei_com/edismax/?q=brand_id:${brandId}&rows=1&version=2.2&start=0&rows=10&indent=on&wt=json&fl=brand_chinese_name,brand_pinyin_name,brand_english_name&group.facet=true";
        $result = $this->getCompressContent($url);
        if(isset($result["grouped"]["unique_id"]["ngroups"]) && $result["grouped"]["unique_id"]["ngroups"] > 0){
            $brandInfo["product_count"] = $result["grouped"]["unique_id"]["ngroups"];
            $brandInfo["brand_english_name"] = $result["response"]["docs"][0]["brand_english_name"];
            $brandInfo["brand_chinese_name"] = $result["response"]["docs"][0]["brand_chinese_name"];
            $brandInfo["brand_pinyin_name"]  = $result["response"]["docs"][0]["brand_pinyin_name"];
        }
        $redis->set("search_indexing_brand_${brandId}", json_encode($brandInfo), 180);
        return $brandInfo;
    }

    /**
     * curl 发送 请求获取 solr全文检索引擎的数据，启用gzip压缩l
     * @author wwpeng
     * @param string $url
     * @return mixed
     */
    private function getCompressContent($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
        $output = curl_exec($ch);
        $output = json_decode($output, 1);
        curl_close($ch);
        return $output;
    }

    /**
     * @param $ids
     * @return null
     */
    public function read_delta_data($ids){
        $store_detail = array();
        $ids_array=array_chunk($ids,50);
        foreach($ids_array as $ids_sub) {
            $detail_temp=$this->getDetails($ids_sub);
            $store_detail=array_merge($store_detail,$detail_temp);
        }
        return $store_detail;
    }

    public function getDetails($ids){
        require_once (PROJECT_ROOT . '/util/JMutil.php');
        $result = JMutil::getThriftClient('MerchantStore', 'getStoreInfoByStoreIds', array($ids));
        $store_detail=json_decode($result['data'],true);
        file_put_contents("/home/logs/delta_import/datalog.txt", date("Y-m-d|H:i:s")." 店铺详情consumed: \n".json_encode($store_detail),FILE_APPEND);
        if ($store_detail !=null) {
            $favCounts = \PHPClient\Text::inst('UserInfo')->setClass('Favourite')->getShopFavCountByShopIds($ids, true);
            if ($favCounts['errorcode'] == 0) {
                $store_count = $favCounts["data"];
                foreach ($store_count as $k => $v) {
                    $store_detail[$k]['fav_count'] = $v;
                }
            } else {
                // throw new Exception('调用获取收藏店铺接口失败，失败代码:' . $favCounts[0]); 记录异常,不抛出
                error_log("时间" . date("Y-m-d|H:i:s") . "调用获取收藏店铺接口失败，失败代码:" . $favCounts[0], 3, "/home/logs/data_import/store_error.log");
            }
            // 填充店铺品牌信息
            foreach($store_detail as $store_id => $store_info){
                $brandStr = $store_info["brand"];
                if(!isset($brandStr) || empty($brandStr)){
                    continue;
                }
                $brandIdArray = json_decode($brandStr);
                foreach($brandIdArray as $brand_id){
                    $brandInfo = $this->getBrandInfo($brand_id);
                    $store_detail[$store_id]["brand_details"][] = $brandInfo;
                }
            }
        }
        return $store_detail;
    }

    /**
     * 索引的字段是根据前端的业务需求
     */
    public function field_transform($data)
    {
        $data_transformed = array();
        foreach ($data as $id => $store) {
            $data_transformed[$id]['id'] = $store['id'];
            $data_transformed[$id]['store_name'] = $store['store_name'];
            $data_transformed[$id]['store_label'] = $store['store_label'];
            $data_transformed[$id]['fav_count'] = (!empty($store['fav_count']))?$store['fav_count']:0;
            if (isset($store['brand']) && ! empty($store['brand'])) {
                $brand_array = json_decode($store['brand']);
                //$data_transformed[$id]['brand'] = array_pop($brand_array);
                $data_transformed[$id]+=array('brand'=>array_values($brand_array));
            }
            $data_transformed[$id]['pc_head_imgurl'] = $store['pc_head_imgurl'];
            $data_transformed[$id]['mobile_head_imgurl'] = $store['mobile_head_imgurl'];
            $data_transformed[$id]['logo_url'] = $store['logo_url'];

            $data_transformed[$id]['flagship_store_type'] = $store['flagship_store_type'];
            $data_transformed[$id]['is_authorization'] = isset($store['is_authorization']) ? $store['is_authorization'] : 0;
            $data_transformed[$id]['is_proprietary'] = isset($store['is_proprietary']) ? $store['is_proprietary'] : 0;
            $data_transformed[$id]['is_pc_store'] = isset($store['is_pc_store']) ? $store['is_pc_store'] : 0;
            $data_transformed[$id]['is_mobile_store'] = isset($store['is_mobile_store']) ? $store['is_mobile_store'] : 0;

            $data_transformed[$id]['jumei_href'] = isset($store['jumeimall_url'])? $store['jumeimall_url'] : "";
            $data_transformed[$id]['h5_href'] = isset($store['h5_url']) ? $store['h5_url'] : "";
            $data_transformed[$id]['pc_href'] = isset($store['pc_url']) ? $store['pc_url'] : "";

            $data_transformed[$id]['sales_volume_30'] = isset($store['sales_volume_30']) ? $store['sales_volume_30'] : 0;
            $data_transformed[$id]['sales_num_30'] = isset($store['sales_num_30']) ? $store['sales_num_30'] : 0;

            //汇总品牌信息
            $productCount = 0;
            $productNames = array();
            if(isset($store['brand_details']) && !empty($store['brand_details']) ){
                foreach($store['brand_details'] as $brand_detail){
                    if(!isset($brand_detail) || !$brand_detail || empty($brand_detail)){
                        continue;
                    }
                    $productCount  += $brand_detail["product_count"];
                    if(isset($brand_detail["brand_english_name"])){
                        $productNames[] = strtolower($brand_detail["brand_english_name"]);
                    }
                    if(isset($brand_detail["brand_chinese_name"])){
                        $productNames[] = strtolower($brand_detail["brand_chinese_name"]);
                    }
                }
            }
            $data_transformed[$id]['product_count'] = $productCount;
            $data_transformed[$id]['brand_name'] = $productNames;
            
            if (isset($store['pc_flagship_status']) && is_numeric($store['pc_flagship_status'])) {
                $data_transformed[$id]['pc_flagship_status_int_dynamic'] = $store['pc_flagship_status'];
            }
            if (isset($store['mobile_flagship_status']) && is_numeric($store['mobile_flagship_status'])) {
                $data_transformed[$id]['mobile_flagship_status_int_dynamic'] = $store['mobile_flagship_status'];
            }
            if (isset($store['store_type']) && !empty($store['store_type'])) {
                $data_transformed[$id]['store_type_string_dynamic'] = $store['store_type'];
                if ($store['store_type'] == 'flagship_store') { // 旗舰店
                    $data_transformed[$id]['store_type_trans_int_dynamic'] = 2;
                } else if ($store['store_type'] == 'specialty_store') { // 专营店
                    $data_transformed[$id]['store_type_trans_int_dynamic'] = 1;
                } else {
                    $data_transformed[$id]['store_type_trans_int_dynamic'] = 0;
                }
            } else {
                $data_transformed[$id]['store_type_trans_int_dynamic'] = 0;
            }

            if (isset($store['search_imgUrl']) && !empty($store['search_imgUrl'])) {
                $data_transformed[$id]['search_imgUrl_string_dynamic'] = $store['search_imgUrl'];
            }
            if (isset($store['promo_start_time']) && !empty($store['promo_start_time'])) {
                $data_transformed[$id]['promo_start_time_long_dynamic'] = $store['promo_start_time'];
            }
            if (isset($store['promo_end_time']) && !empty($store['promo_end_time'])) {
                $data_transformed[$id]['promo_end_time_long_dynamic'] = $store['promo_end_time'];
            }
            if (isset($store['promo_text']) && !empty($store['promo_text'])) {
                $data_transformed[$id]['promo_text_string_dynamic'] = $store['promo_text'];
            }
            if (isset($store['store_content']) && !empty($store['store_content'])) {
                $data_transformed[$id]['store_content_string_dynamic'] = $store['store_content'];
            }

            JMutil::valid_check("store", $id, $data_transformed);
        }
        
        return $data_transformed;
    }

    public function write_xml($data)
    {
        global $CONFIG;
        $file_path = $CONFIG['file']['path']['store'];
        if (! is_dir($file_path)) {
            mkdir($file_path, 0777, true);
        }
        $time = date("Y-m-d|H:i:s");
        $file = $file_path . "/" . "store_full_import_" . $time . ".xml";
        
        // 需要建立data_import目录，然后给写权限
        require_once (PROJECT_ROOT . '/util/util_write.php');
        if (! ImportWrite::write_xml_add($data, $file)) {
            error_log("时间" . $time . "store全量索引失败！！！", 3, "/home/logs/data_import/store_import_error.log");
        } else {
            // 将本次索引时间记录下来
            $index_time = file_get_contents($CONFIG['file']['path']['data'] . "/store.properties");
            $last_time = array();
            if (! empty($index_time)) {
                $tmp = explode("\n", $index_time);
                foreach ($tmp as $value) {
                    $tmp2 = explode("=", $value);
                    if ($tmp2[0] == "store") {
                        $tmp2[1] = $time;
                    }
                    $last_time[$tmp2[0]] = $tmp2[0] . "=" . $tmp2[1];
                }
            }
            
            if (! isset($last_time['store'])) {
                $last_time['store'] = "store=" . $time;
            }
            file_put_contents($CONFIG['file']['path']['data'] . "/store.properties", implode("\n", $last_time));
        }
    }

    public function get_dir_path(){
        // not necessary
    }
}