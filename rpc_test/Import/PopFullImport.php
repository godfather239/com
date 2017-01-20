<?php
require_once (PROJECT_ROOT . "/Import/load_source_data.php");
require_once (PROJECT_ROOT . '/util/JMutil.php');
class PopFullImport implements LoadSourceData
{
    public function getIds($flag,$num=50){
        $hash_ids =JMutil::retryClient('JumeiProduct_Search_Read_Deals', 'getHashIdOfPOPByLimit', array($flag,$num));
        ImportWrite::log_info("type:pop,flag:{$flag},num:{$num},ids:{$hash_ids}");
        return $hash_ids;
    }
    
    public function getDetails($ids){
        $ids=array_filter($ids);
        if(count($ids) > 0){
            $result = JMutil::retryClient('JumeiProduct_Search_Read_Deals', 'getPopDetailForSearch', array($ids));
            return $result;
        } else {
            return null;
        }
    }

    public function detail2Product($deal_detail,$result,$ids){
        $result=html_entity_decode($result);
        $product = json_decode($result,true);
        if(!empty($product)){
            // 促销规则
            require_once (PROJECT_ROOT . '/util/Promotion.php');
            $product = JMutil::two_dimensional_array_merge(Promotion::getPromotion($ids,"pop") , $product);
            // 专场信息
            require_once (PROJECT_ROOT . '/util/ActivityUtil.php');
            $queryId=array($ids,array(),array());
            $product=ActivityUtil::mergeActivity($product,ActivityUtil::getActivity($queryId,"deal"),"hash_id");

            //获取系列
            require_once(PROJECT_ROOT . '/util/Series.php');
            $params = JMutil::array_productId_storeId($product);
            $product = JMutil::array_merge_for_series(SeriesTransform::getSeries($params), $product);

            // 销售信息
            // require_once (PROJECT_ROOT . '/util/Sales.php');
            // $pids = JMutil::collectionKeys($product, "product_id");
            // $product = JMutil::mergeByIndenity($product, Sales::getSales($pids) ,"product_id");

            $deal_detail += $this->field_transform($product);
        }
        return $deal_detail;
    }

    public function read_original_data(){
        require_once (PROJECT_ROOT . '/util/util_write.php');
        $file_path = $this->get_dir_path();

        $tmp_file = "{$file_path}/tmp.xml";
        $fp = fopen($tmp_file, "w");
        if (!$fp) {
            error_log("时间" . date("Y-m-d|H:i:s") . "Failed to open file {$tmp_file}", 3, "/home/logs/data_import/pop_import_error.log");
            return array();
        }
        fwrite($fp, "<add>");
        
        $flag=0;
        while($flag!=-1){
            $hash_ids =$this->getIds($flag);
            if($hash_ids=="[[],[]]"){
                $flag=-1;
            }else{
                $deal_detail = array();
                $hash_ids_arrays = json_decode($hash_ids,true);
                if(!empty($hash_ids_arrays)) {
                    $flag = $hash_ids_arrays[0]['minId'];
                    $ids = $hash_ids_arrays[1];
                    $result =$this->getDetails($ids);
                    $deal_detail=$this->detail2Product($deal_detail,$result,$ids);
                    $xml = ImportWrite::get_xml_body($deal_detail);
                    if (!empty($xml)) {
                        fwrite($fp, $xml);
                    }
                }
            }
        }
        //获取一起团hash_id
        $fp=Yqt::getYQTDeal($fp,"pop_deal",$this);

        fwrite($fp, "</add>");
        fclose($fp);
        $time = date("Y-m-d|H:i:s");
        $file = $file_path . "/" . "pop_full_import_" . $time . ".xml";
        rename($tmp_file, $file);
        ImportWrite::update_timestamp('pop', $time);
        return true;
    }

    public function read_delta_data($pORhIds){
        $detail = array();
        $pORhIds = JMutil::expandArrayByDelimiter($pORhIds, ",");
        $ids_array = array_chunk($pORhIds,50);
        foreach($ids_array as $ids){
            $result =$this->getDetails($ids);
            $detail = $this->detail2Product($detail,$result,$ids);
        }

        return $detail;
    }

    public function field_transform($data) {
         global $CONFIG;
         require_once (PROJECT_ROOT . '/util/product_property_transform.php');
         $times = time();
    	 $data_transformed = array();
    	 foreach ($data as $id => $product) {
             if(!isset($product['deal_id']) || empty($product['deal_id'])){
                 continue;
             }
    	 	$data_transformed[$id]['doc_id'] = "pop_" . $product['deal_id'];
    	 	$data_transformed[$id]['deal_id'] = $product['deal_id'];
    	 	$data_transformed[$id]['doc_type'] = "pop";
    	 	$data_transformed[$id]['market_price'] = $product['original_price'];
    	 	$data_transformed[$id]['jumei_price'] = ProductPropertyTransform::jumei_price_transform($product,"pop");
            $data_transformed[$id]['sort_price'] = $data_transformed[$id]['jumei_price'];
    	 	$data_transformed[$id]['discount'] = $product['discount'];

    	 	$data_transformed[$id]['start_time'] = $product['start_time'];
    	 	$data_transformed[$id]['sort_start_date'] = $product['start_time'];
    	 	$data_transformed[$id]['end_time'] = $product['end_time'];

    	 	$data_transformed[$id]['hash_id'] = $product['hash_id'];

    	 	$data_transformed[$id]['status'] = $product['status'];

    	 	$data_transformed[$id]['short_name'] = $product['short_name'];
    	 	$data_transformed[$id]['display_name_highlight'] = $product['short_name'];

    	 	$data_transformed[$id]['category'] = $product['category'];
    	 	$data_transformed[$id]['show_category'] = $product['show_category'];

    	 	$data_transformed[$id]['product_id'] = $product['product_id'];
    	 	$data_transformed[$id]['unique_id'] = $product['product_id'];

    	 	$data_transformed[$id]['platform'] = $product['platform'];
    	 	$data_transformed[$id]['deal_group_label'] = $product['deal_group_label'];

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

            if(isset($product['value']) && !empty($product['value'])){
                $data_transformed[$id]['hover_picture'] = $product['value'][0]['value'];
            }

            //tag_id为1和2在前端判断都是等价的，这边只索引一个id
            if(isset($product['tag_id']) && !empty($product['tag_id'])){
                $data_transformed[$id]['tag_id'] = $product['tag_id'];
            }
             $data_transformed[$id]['new_product_flag_int_dynamic'] = ProductPropertyTransform::new_product_transform($product);

             if(isset($product['sale_forms']) && !empty($product['sale_forms'])){
                 $data_transformed[$id]['sale_forms']=$product['sale_forms'];
             }


//            $is_yqt = (isset($product['sale_forms']) && $product['sale_forms']=="yqt");

//             if(!$is_yqt){
//                 if($product['start_time']>$times){
//                     $data_transformed[$id]['sale_forms']="pre_hot";
//                 }else{
//                     $data_transformed[$id]['sale_forms']="normal";
//                 }
//             }else{
//                 $data_transformed[$id]['sale_forms']="yqt";
//             }

            $data_transformed[$id]['merchant_id'] = isset($product['merchant_id'])?$product['merchant_id']:0;
            $data_transformed[$id]['wish_number'] = isset($product['wish_number'])?$product['wish_number']:0;
//            $data_transformed[$id]['wish_number'] = isset($product['real_wish_number'])?$product['real_wish_number']:0;
            $data_transformed[$id]['is_published_price'] = isset($product['is_published_price'])?$product['is_published_price']:1;
            //品牌，分类，功效
            $data_transformed[$id] += ProductPropertyTransform::brand_transform($product);
            $data_transformed[$id] += ProductPropertyTransform::category_transform($product);


            //库存信息相关
            $data_transformed[$id]['is_available_bj'] = 1;
            $data_transformed[$id]['is_available_cd'] = 1;
            $data_transformed[$id]['is_available_sh'] = 1;
            $data_transformed[$id]['is_available_gz'] = 1;
            
            //库存信息相关
            if (!empty($product['is_available_site'])) {
                foreach ($product['is_available_site'] as $site) {
                    $data_transformed[$id]['is_available_' . $site] = 1;
                }
            }
            
            $data_transformed[$id]['is_sellable'] = max($data_transformed[$id]['is_available_bj'],$data_transformed[$id]['is_available_sh'],$data_transformed[$id]['is_available_cd'],$data_transformed[$id]['is_available_gz']);
            $data_transformed[$id]['countries'] = isset($product['countries'])?$product['countries']:0;
            $data_transformed[$id]['high_priority_sort'] =0;

             //预热
             if( $product['start_time']>$times){
                 $data_transformed[$id]['high_priority_sort'] =1;
             }
            //不可售
            if($data_transformed[$id]['is_sellable']==0||$product['status']==2||$product['end_time']<$times){
                $data_transformed[$id]['high_priority_sort'] =2;
            }


            //促销信息
             $data_transformed[$id]+= ProductPropertyTransform::promotion_transform($product);
             $data_transformed[$id]+= ProductPropertyTransform::promotion_rule_transform($product);

            //销售额
            $data_transformed[$id]['sale_amount']= $product['history_sale_amount'];
             //商品对应的店铺id
             if (isset($product['store_id']) && !empty($product['store_id'])) {
                 $data_transformed[$id]['store_id'] = $product['store_id'];
                 foreach($product['store_id'] as $store_id){
                     $data_transformed[$id]["store_id_".$store_id."_string_dynamic"]=$store_id;
                 }
             }

             if (isset($product['activityId']) && !empty($product['activityId'])) {
                 $data_transformed[$id]['activityId'] = $product['activityId'];
             }
             $data_transformed[$id]['deal_sort'] =0;

             // $data_transformed[$id]['real_30day_mall_sale_volume'] = isset($product['real_30day_mall_sale_volume']) ? $product['real_30day_mall_sale_volume'] : 0;
             // $data_transformed[$id]['real_30day_deal_sale_volume'] = isset($product['real_30day_deal_sale_volume']) ? $product['real_30day_deal_sale_volume'] : 0;
             // 记录30天销售信息
             $data_transformed[$id]['real_30day_buyer_number'] = isset($product['real_30day_buyer_number']) ? $product['real_30day_buyer_number'] : 0;
             $data_transformed[$id]['fake_30day_buyer_number'] = isset($product['fake_30day_buyer_number']) ? $product['fake_30day_buyer_number'] : 0;
             $data_transformed[$id]['real_30day_sales_amount'] = isset($product['real_30day_sales_amount']) ? $product['real_30day_sales_amount'] : 0;

             // 销售排序信息
             $data_transformed[$id]['sort_sales_volume'] = $data_transformed[$id]['real_30day_buyer_number'];
             $data_transformed[$id]['sort_sales_amount'] = $data_transformed[$id]['real_30day_sales_amount'];

             // 原有销售信息(考虑废弃)
             $data_transformed[$id]['buyer_number'] = $product['buyer_number'];
             $data_transformed[$id]['mall_real_buyer_number'] = $product['buyer_number'];
             $data_transformed[$id]['deal_real_buyer_number'] = $product['real_buyer_number'];

             $data_transformed[$id]['shipping_system_id'] = $product['shipping_system_id'];

             // 增加地区直邮策略搜索关键字,如香港直邮,澳门直邮等关键字
             $shipping_metatext_mapping = $CONFIG['shipping_system_metatext'];
             if(isset($product["shipping_system_id"]) && isset($shipping_metatext_mapping[$product["shipping_system_id"]])) {
                 // 添加直邮关键字
                 $shipping_info = $shipping_metatext_mapping[$product["shipping_system_id"]];
                 $data_transformed[$id]['search_meta_text_custom'] .=
                     !empty($data_transformed[$id]['search_meta_text_custom']) ? ",".$shipping_info : $shipping_info ;
             }

             // 为预售deal增加
             if(isset($product['saved_amount']) && !empty($product['saved_amount'])){
                 // 比平时优惠金额
                 $data_transformed[$id]['saved_amount'] = $product['saved_amount'];
             }
             if(isset($product['payment_start_time']) && !empty($product['payment_start_time'])) {
                 // 尾款支付开始时间
                 $data_transformed[$id]['payment_start_time'] = $product['payment_start_time'];
             }
             if(isset($product['payment_end_time']) && !empty($product['payment_end_time'])) {
                 // 尾款支付结束时间
                 $data_transformed[$id]['payment_end_time'] = $product['payment_end_time'];
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
             if(isset($product['is_new_tag_time']) && !empty($product['is_new_tag_time'])) {
                 // 上新时间
                 $data_transformed[$id]['is_new_tag_time'] = $product['is_new_tag_time'];
             }
             // 商品是否有短视频标记
             $data_transformed[$id]['has_short_video_string_dynamic'] = $product['has_short_video'];
             //商品初始库存
             $data_transformed[$id]['stocks_int_dynamic'] = $product['stocks'];
             
             if (isset($product['shipping_system_id']) && is_numeric($product['shipping_system_id'])) {
                 $data_transformed[$id]['shipping_system_id'] = $product['shipping_system_id'];
             }
             ProductPropertyTransform::dx_img_transfrom($product, $id, $data_transformed);
             ProductPropertyTransform::sale_status_transform($product, $id, $data_transformed);
             ProductPropertyTransform::relation_deal_transform($product, $id, $data_transformed);
             ProductPropertyTransform::sort_field_transform($product, $id, $data_transformed);
             JMutil::valid_check("product", $id, $data_transformed);
    	 }

         return $data_transformed;
    }
    public function write_xml($data) {
//    	//写文件的时候，文件名是不一样的；
//        global $CONFIG;
//        $file_path = $CONFIG['file']['path']['pop'];
//        if (!is_dir($file_path)) {
//            mkdir($file_path, 0777, true);
//        }
//        $time = date("Y-m-d|H:i:s");
//        $file = $file_path . "/" . "pop_full_import_" . $time . ".xml";
//        
//        //需要建立data_import目录，然后给写权限
//        require_once (PROJECT_ROOT . '/util/util_write.php');
//        if (!ImportWrite::write_xml_add($data, $file)) {
//            error_log("时间" . $time . "pop全量索引失败！！！", 3, "/home/logs/data_import/pop_import_error.log");
//        }else{
//            //将本次索引时间记录下来
//            $index_time = file_get_contents($CONFIG['file']['path']['data'] . "/data.properties");
//            $last_time = array();
//            if(!empty($index_time)){
//                $tmp = explode("\n",$index_time);
//                foreach ($tmp as $value) {
//                    $tmp2 = explode("=",$value);
//                    if($tmp2[0] == "pop"){
//                        $tmp2[1] = $time;
//                    }
//                    $last_time[$tmp2[0]] = $tmp2[0] . "=" .$tmp2[1];                    
//                }
//            }
//
//            if(!isset($last_time['pop'])){
//                $last_time['pop'] = "pop=".$time;
//            }
//            file_put_contents($CONFIG['file']['path']['data'] . "/data.properties", implode("\n",$last_time));
//        }
    }
    
    public function get_dir_path()
    {
        global $CONFIG;
        $file_path = $CONFIG['file']['path']['pop'];
        if (!is_dir($file_path)) {
            mkdir($file_path, 0777, true);
        }
        return $file_path;
    }

}