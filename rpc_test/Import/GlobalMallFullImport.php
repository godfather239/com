<?php
require_once (PROJECT_ROOT . "/Import/load_source_data.php");
require_once (PROJECT_ROOT . '/util/JMutil.php');
class GlobalMallFullImport implements LoadSourceData
{
    public function getIds($flag,$num=50){
        $hash_ids =JMutil::retryClient('JumeiProduct_Search_Read_Deals', 'getMallDataWithMallIdAndLimit', array($flag,$num));
        ImportWrite::log_info("type:global_mall,flag:{$flag},num:{$num},ids:{$hash_ids}");
        return $hash_ids;
    }

    public function getDetails($ids){
        $ids=array_filter($ids);
        if(count($ids)>0){
        $result =JMutil::retryClient('JumeiProduct_Search_Read_Deals', 'getGlobalMallDetailForSearch', array($ids));
        return $result;
        }
        else return null;
    }

    public function detail2Product($mall_products_detail,$result,$ids){
        $result=html_entity_decode($result);
        $product = json_decode($result,true);
        if(!empty($product)) {
            // 促销规则
            require_once (PROJECT_ROOT . '/util/Promotion.php');

            $product = JMutil::array_merge_for_globalmall(Promotion::getPromotion($ids, "globalmall") , $product);
            require_once (PROJECT_ROOT . '/util/ActivityUtil.php');
            //海淘mall需要使用mall_id
            $mall_id = array_keys($product);
            $mall_id_array = array_chunk($mall_id, 50);
            // 销售信息
            // require_once (PROJECT_ROOT . '/util/Sales.php');
            // $product = JMutil::mergeByIndenity($product, Sales::getSales($ids) ,"product_id");

            foreach($mall_id_array as $mids){
                $queryId=array(array(),array(),$mids);
                $product=ActivityUtil::mergeActivity($product,ActivityUtil::getActivity($queryId,"globalMall"),"mall_id");

                //获取系列
                require_once(PROJECT_ROOT . '/util/Series.php');
                $params = JMutil::array_productId_storeId($product);
                $product = JMutil::array_merge_for_series(SeriesTransform::getSeries($params), $product);

                $mall_products_detail += $this->field_transform($product);
            }
        }
        return $mall_products_detail;
    }

    public function read_original_data() {
        require_once (PROJECT_ROOT . '/util/util_write.php');
        $file_path = $this->get_dir_path();


        $tmp_file = "{$file_path}/tmp.xml";
        $fp = fopen($tmp_file, "w");
        if (!$fp) {
            error_log("时间" . date("Y-m-d|H:i:s") . "Failed to open file {$tmp_file}", 3, "/home/logs/data_import/global_mall_import_error.log");
            return array();
        }
        fwrite($fp, "<add>");
        
        $flag=0;
        while($flag!=-1){
            $product_ids =$this->getIds($flag);
            if($product_ids=="[{\"minId\":".$flag."},[]]") {
                $flag = -1;
            }else{
                $mall_products_detail = array();
                $product_ids_arrays = json_decode($product_ids,true);
                if(!empty($product_ids_arrays)){
                    $flag=$product_ids_arrays[0]['minId'];
                    $ids=$product_ids_arrays[1];
                    $result=$this->getDetails($ids);
                    $mall_products_detail=$this->detail2Product($mall_products_detail,$result,$ids);
                    $xml = ImportWrite::get_xml_body($mall_products_detail);
                    if (!empty($xml)) {
                        fwrite($fp, $xml);
                    }
                }
            }
        }
        fwrite($fp, "</add>");
        fclose($fp);
        $time = date("Y-m-d|H:i:s");
        $file = $file_path . "/" . "global_mall_full_import_" . $time . ".xml";
        rename($tmp_file, $file);
        ImportWrite::update_timestamp('global_mall', $time);
        return true;
    }

    public function read_delta_data($pORhIds){
        $detail = array();
        $ids_array=array_chunk($pORhIds,50);
        foreach($ids_array as $ids){
            $result =$this->getDetails($ids);
            $detail = $this->detail2Product($detail,$result,$ids);
        }

        return $detail;
    }


    public function field_transform($data) {
        global $CONFIG;
        require_once (PROJECT_ROOT . '/util/product_property_transform.php');

        $data_transformed = array();
        
        //获取到的原始商品字段跟要建立的索引字段之间不一样都要进行转化
        foreach ($data as $id => $product) {
            $data_transformed[$id]['doc_id'] = 'global_mall_' . $product['mall_id'];
            $data_transformed[$id]['doc_type'] = 'global_mall';
            $data_transformed[$id]['product_id'] = $product['product_id'];
            $data_transformed[$id]['mall_id'] = $product['mall_id'];
            $data_transformed[$id]['unique_id'] = $product['product_id'];
            $data_transformed[$id]['mall_product_name'] = $product['product_short_name'];
            $data_transformed[$id]['display_name_highlight'] = $product['product_short_name'];
            
            $data_transformed[$id]['market_price'] = $product['sku_max_market_price'];
            $data_transformed[$id]['jumei_price'] = ProductPropertyTransform::jumei_price_transform($product,"global_mall");
            $data_transformed[$id]['sort_price'] = $data_transformed[$id]['jumei_price'];
            $data_transformed[$id]['discount'] = $product['min_discount'];
            if (isset($product['start_time']) && !empty($product['start_time'])) {
                $data_transformed[$id]['sort_start_date'] = $product['start_time'];
            }
                  
            $data_transformed[$id]['status'] = $product['status'];

            $data_transformed[$id]['search_meta_text_custom'] = $product['search_meta_text_custom'];

            $data_transformed[$id]+= ProductPropertyTransform::series_transform($product);

            //货价
            if (isset($product['value_of_goods']) && is_numeric($product['value_of_goods'])){
                $data_transformed[$id]['value_of_goods_float_dynamic'] = $product['value_of_goods'];
            }

            //单包价
            if (isset($product['single_package_price']) && is_numeric($product['single_package_price'])){
                $data_transformed[$id]['single_package_price_float_dynamic'] = $product['single_package_price'];
            }
            //包数量
            if (isset($product['single_package_num']) && is_numeric($product['single_package_num'])) {
                $data_transformed[$id]['single_package_num_int_dynamic'] = $product['single_package_num'];
            }

            // 海淘商品增加category [normal, new_combination_global]
            $data_transformed[$id]['category'] = $product['category'];
            $data_transformed[$id]['shipping_system_id'] = $product['shipping_system_id'];

            // 增加地区直邮策略搜索关键字,如香港直邮,澳门直邮等关键字
            $shipping_metatext_mapping = $CONFIG['shipping_system_metatext'];
            if(isset($product["shipping_system_id"]) && isset($shipping_metatext_mapping[$product["shipping_system_id"]])) {
                // 如要search_meta_text_custom为空,则创建一个空数组
                // $data_transformed[$id]['search_meta_text_custom'] =
                //     isset($data_transformed[$id]['search_meta_text_custom']) ? $data_transformed[$id]['search_meta_text_custom'] : [];
                // 添加直邮关键字
                $shipping_info = $shipping_metatext_mapping[$product["shipping_system_id"]];
                $data_transformed[$id]['search_meta_text_custom'] .=
                    !empty($data_transformed[$id]['search_meta_text_custom']) ? ",".$shipping_info : $shipping_info ;
            }

            //品牌，分类，功效
            $data_transformed[$id]+= ProductPropertyTransform::brand_transform($product);
            $data_transformed[$id]+= ProductPropertyTransform::category_transform($product);
            $data_transformed[$id]+= ProductPropertyTransform::function_transform($product);
            
            //商品是否可售，站点
            $data_transformed[$id]['is_available_bj'] = 0;
            $data_transformed[$id]['is_available_cd'] = 0;
            $data_transformed[$id]['is_available_sh'] = 0;
            $data_transformed[$id]['is_available_gz'] = 0;
            
            //库存信息相关
            if (!empty($product['is_available_site'])) {
                foreach ($product['is_available_site'] as $site) {
                    $data_transformed[$id]['is_available_' . $site] = 1;
                }
            }
            $data_transformed[$id]['is_sellable'] = max($data_transformed[$id]['is_available_bj'],$data_transformed[$id]['is_available_sh'],$data_transformed[$id]['is_available_cd'],$data_transformed[$id]['is_available_gz']);
            //线上数据会有area_code为0的情况，所以这边要判断一下。
            if(!empty($product['area_code'])){
                $data_transformed[$id]['area_code'] = $product['area_code'];
            }
            $data_transformed[$id]['countries'] = isset($product['countries'])?$product['countries']:0;
            $data_transformed[$id]['high_priority_sort'] =0;
            if($data_transformed[$id]['is_sellable']==0){
                $data_transformed[$id]['high_priority_sort'] =2;
            }
            if (isset($product['activityId']) && !empty($product['activityId'])) {
                $data_transformed[$id]['activityId'] = $product['activityId'];
            }
            $data_transformed[$id]['deal_sort'] =1;

            //促销信息
            $data_transformed[$id]+= ProductPropertyTransform::promotion_transform($product);
            $data_transformed[$id]+= ProductPropertyTransform::promotion_rule_transform($product);

            // $data_transformed[$id]['real_30day_mall_sale_volume'] = isset($product['real_30day_mall_sale_volume']) ? $product['real_30day_mall_sale_volume'] : 0;
            // $data_transformed[$id]['real_30day_deal_sale_volume'] = isset($product['real_30day_deal_sale_volume']) ? $product['real_30day_deal_sale_volume'] : 0;
            // 记录30天销售信息
            $data_transformed[$id]['real_30day_buyer_number'] = isset($product['real_30day_buyer_number']) ? $product['real_30day_buyer_number'] : 0;
            $data_transformed[$id]['fake_30day_buyer_number'] = isset($product['fake_30day_buyer_number']) ? $product['fake_30day_buyer_number'] : 0;
            $data_transformed[$id]['real_30day_sales_amount'] = isset($product['real_30day_sales_amount']) ? $product['real_30day_sales_amount'] : 0;

            // 销售排序信息
            $data_transformed[$id]['sort_sales_volume'] = $data_transformed[$id]['real_30day_buyer_number'];
            $data_transformed[$id]['sort_sales_amount'] = $data_transformed[$id]['real_30day_sales_amount'];
            $data_transformed[$id]['mall_real_buyer_number'] = $data_transformed[$id]['real_30day_buyer_number'];

            // 原产国
            if (isset($product['countries_name']) && !empty($product['countries_name'])) {
                $data_transformed[$id]['countries_name_string_dynamic'] = $product['countries_name'];  // dynamic field for solr
            }

            // 规格信息
            if (isset($product['sku']) && !empty($product['sku'])) {
                $attributes = array();
                $sizes = array();
                $properties = array();
                foreach ($product['sku'] as $sku_no => $vals) {
                    if (isset($vals['attribute']) && !empty($vals['attribute'])) {
                        $attributes[] = $vals['attribute'];
                    }
                    if (isset($vals['size']) && !empty($vals['size'])) {
                        $sizes[] = $vals['size'];
                    }
                    if (isset($vals['property']) && !empty($vals['property'])) {
                        $properties[] = $vals['property'];
                    }
                }
                // 在solr中以dynamic field存储
                if (!empty($attributes)) {
                    $data_transformed[$id]['sku_attribute_mv_text_dynamic'] = is_array($attributes) ? array_unique($attributes) : $attributes;
                }
                if (!empty($sizes)) {
                    $data_transformed[$id]['sku_size_mv_text_dynamic'] = is_array($sizes) ? array_unique($sizes) : $sizes;
                }
                if (!empty($properties)) {
                    $data_transformed[$id]['sku_property_mv_text_dynamic'] = is_array($properties) ? array_unique($properties) : $properties;
                }
            }

            //tag_id为1和2在前端判断都是等价的，这边只索引一个id
            if(isset($product['tag_id']) && !empty($product['tag_id'])){
                $data_transformed[$id]['tag_id'] = $product['tag_id'];
            }
            $data_transformed[$id]['new_product_flag_int_dynamic'] = ProductPropertyTransform::new_product_transform($product);

            if(isset($product['is_new_tag_time']) && !empty($product['is_new_tag_time'])) {
                // 上新时间
                $data_transformed[$id]['is_new_tag_time'] = $product['is_new_tag_time'];
            }

            // 商品是否有短视频标记
            $data_transformed[$id]['has_short_video_string_dynamic'] = $product['has_short_video'];

            //商品对应的店铺id
            if (isset($product['store_id']) && !empty($product['store_id'])) {
                $data_transformed[$id]['store_id'] = $product['store_id'];
                foreach($product['store_id'] as $store_id){
                    $data_transformed[$id]["store_id_".$store_id."_string_dynamic"]=$store_id;
                }
            }
            ProductPropertyTransform::dx_img_transfrom($product, $id, $data_transformed);
            //标记商品状态，预热,在售，过期
            ProductPropertyTransform::sale_status_transform($product, $id, $data_transformed);
            ProductPropertyTransform::sort_field_transform($product, $id, $data_transformed);
            JMutil::valid_check("product", $id, $data_transformed);

        }
        return $data_transformed;
    }

    public function write_xml($data) {
        
//        //写文件的时候，文件名是不一样；
//        global $CONFIG;
//        $file_path = $CONFIG['file']['path']['global_mall'];
//        if (!is_dir($file_path)) {
//            mkdir($file_path, 0777, true);
//        }
//        $time = date("Y-m-d|H:i:s");
//        $file = $file_path . "/" . "global_mall_full_import_" . $time . ".xml";
//        
//        //需要建立data_import目录，然后给写权限
//        require_once (PROJECT_ROOT . '/util/util_write.php');
//        if (!ImportWrite::write_xml_add($data, $file)) {
//            error_log("时间" . $time . "海淘商城全量索引失败！！！", 3, "/home/logs/data_import/global_mall_import_error.log");
//        }else{
//            //将本次索引时间记录下来
//            $index_time = file_get_contents($CONFIG['file']['path']['data'] . "/data.properties");
//            $last_time = array();
//            if(!empty($index_time)){
//                $tmp = explode("\n",$index_time);
//                foreach ($tmp as $value) {
//                    $tmp2 = explode("=",$value);
//                    if($tmp2[0] == "global_mall"){
//                        $tmp2[1] = $time;
//                    }
//                    $last_time[$tmp2[0]] = $tmp2[0] . "=" .$tmp2[1];                    
//                }
//            }
//
//            if(!isset($last_time['global_mall'])){
//                $last_time['global_mall'] = "global_mall=".$time;
//            }
//            file_put_contents($CONFIG['file']['path']['data'] . "/data.properties", implode("\n",$last_time));
//        }
    }

    public function get_dir_path()
    {
        global $CONFIG;
        $file_path = $CONFIG['file']['path']['global_mall'];
        if (!is_dir($file_path)) {
            mkdir($file_path, 0777, true);
        }
        return $file_path;
    }

}