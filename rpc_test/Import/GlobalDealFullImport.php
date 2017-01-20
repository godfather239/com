<?php
require_once (PROJECT_ROOT . "/Import/load_source_data.php");
require_once (PROJECT_ROOT . '/util/JMutil.php');
require_once (PROJECT_ROOT . '/util/Yqt.php');
class GlobalDealFullImport implements LoadSourceData
{
    public function getIds($flag,$num=50){
        $hash_ids =JMutil::retryClient('JumeiProduct_Search_Read_Deals', 'getGlobalDealDatasWithDealIdAndLimit', array($flag,$num));
        ImportWrite::log_info("type:global_deal,flag:{$flag},num:{$num},ids:{$hash_ids}");
        return $hash_ids;
    }

    public function getDetails($ids){
        $ids = array_filter($ids);
        if(count($ids)>0){
            $result =JMutil::retryClient('JumeiProduct_Search_Read_Deals', 'getGlobalDealDetailForSearch', array($ids));
            return $result;
        }else{
            return null;
        }
    }

    public function detail2Product($deal_detail,$result,$ids){
        $result=html_entity_decode($result);
        $product = json_decode($result,true);
        if(!empty($product)){
            // #Ticket 101695 之前调用促销的方法只能取到对应的海淘自营deal的,无法获取到海淘第三方deal
            $global_deal_self_ids = array();
            $global_deal_pop_ids = array();
            foreach($product as $key => $item){     // 这里的$product其实是一个数组 key是hash_id,value是详情
                if($item['category'] == 'global'){  // 如果是海淘商品,并且categor为global,则表示其为海淘pop deal,对应促销接口类型为:jumeiglobalmedia
                    $global_deal_pop_ids[] = $key;
                }else{                              // 反之,则是淘泊自营deal,对应促销接口类型为:jumeiglobal
                    $global_deal_self_ids[] = $key;
                }
            }

            //获取系列
            require_once(PROJECT_ROOT . '/util/Series.php');
            $params = JMutil::array_productId_storeId($product);
            $product = JMutil::array_merge_for_series(SeriesTransform::getSeries($params), $product);

            require_once (PROJECT_ROOT . '/util/Promotion.php');
            //　获取淘海POPDeal的促销规则
            if(!empty($global_deal_pop_ids)){
                $product = JMutil::two_dimensional_array_merge(Promotion::getPromotion($global_deal_pop_ids,"jumeiglobalmedia") , $product);
            }
            //　获取淘海自营Deal的促销规则
            if(!empty($global_deal_self_ids)){
                $product = JMutil::two_dimensional_array_merge(Promotion::getPromotion($global_deal_self_ids,"jumeiglobal") , $product);
            }

            // 活动信息
            require_once (PROJECT_ROOT . '/util/ActivityUtil.php');
            $queryId= array($ids,array(),array());
            $product= ActivityUtil::mergeActivity($product,ActivityUtil::getActivity($queryId,"deal"),"hash_id");

            $deal_detail += $this->field_transform($product);
        }
        return $deal_detail;
    }

    public function read_original_data() {
        require_once (PROJECT_ROOT . '/util/util_write.php');
        $file_path = $this->get_dir_path();

        $tmp_file = "{$file_path}/tmp.xml";
        $fp = fopen($tmp_file, "w");
        if (!$fp) {
            error_log("时间" . date("Y-m-d|H:i:s") . "Failed to open file {$tmp_file}", 3, "/home/logs/data_import/global_deal_import_error.log");
            return array();
        }
        fwrite($fp, "<add>");
        
        $flag=0;
        while($flag!=-1){
            $hash_ids =$this->getIds($flag);
            if($hash_ids=="[{$flag},[]]"){
                $flag=-1;
            }else{
                $deal_detail = array();
                $hash_ids_arrays = json_decode($hash_ids,true);
                if(!empty($hash_ids_arrays)) {
                    $flag = $hash_ids_arrays[0];
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
        $fp=Yqt::getYQTDeal($fp,"global_deal",$this);
        $fp=Yqt::getYQTDeal($fp,"global_pop",$this);

        fwrite($fp, "</add>");
        fclose($fp);
        $time = date("Y-m-d|H:i:s");
        $file = $file_path . "/" . "global_deal_full_import_" . $time . ".xml";
        rename($tmp_file, $file);
        ImportWrite::update_timestamp('global_deal', $time);
        return true;
    }

    public function read_delta_data($pORhIds){
        $detail = array();
        $pORhIds = JMutil::expandArrayByDelimiter($pORhIds, ",");
        $ids_array = array_chunk($pORhIds,50);
        foreach($ids_array as $ids){
            $result = $this->getDetails($ids);
            $detail = $this->detail2Product($detail,$result,$ids);
        }

        return $detail;
    }


    /**
     * 
     *索引的字段是根据前端的业务需求 
     * 
     * */

    public function field_transform($data) {
         global $CONFIG;
         require_once (PROJECT_ROOT . '/util/product_property_transform.php');
         $times = time();
    	 $data_transformed = array();
    	 foreach ($data as $id => $product) {
    	 	$data_transformed[$id]['doc_id'] = "global_deal_" . $product['deal_id'];
    	 	$data_transformed[$id]['deal_id'] = $product['deal_id'];
    	 	$data_transformed[$id]['doc_type'] = "global_deal";

    	 	$data_transformed[$id]['market_price'] = $product['sku_max_market_price'];
    	 	$data_transformed[$id]['jumei_price'] = ProductPropertyTransform::jumei_price_transform($product,"global_deal");
            $data_transformed[$id]['sort_price'] = $data_transformed[$id]['jumei_price'];
    	 	$data_transformed[$id]['discount'] = $product['min_discount'];
    	 	$data_transformed[$id]['wish_number'] = isset($product['wish_number'])?$product['wish_number']:0;
    	 	$data_transformed[$id]['start_time'] = $product['start_time'];
    	 	$data_transformed[$id]['sort_start_date'] = $product['start_time'];
    	 	$data_transformed[$id]['end_time'] = $product['end_time'];

    	 	$data_transformed[$id]['hash_id'] = $product['hash_id'];

    	 	$data_transformed[$id]['status'] = $product['status'];

    	 	$data_transformed[$id]['short_name'] = $product['short_name'];
    	 	$data_transformed[$id]['display_name_highlight'] = $product['short_name'];

    	 	$data_transformed[$id]['category'] = $product['category'];
    	 	$data_transformed[$id]['show_category'] = $product['show_category'];

    	 	$data_transformed[$id]['shipping_system_id'] = $product['shipping_system_id'];

    	 	$data_transformed[$id]['product_id'] = $product['product_id'];
    	 	$data_transformed[$id]['unique_id'] = $product['product_id'];

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

             // 增加地区直邮策略搜索关键字,如香港直邮,澳门直邮等关键字
            $shipping_metatext_mapping = $CONFIG['shipping_system_metatext'];
             if(isset($product["shipping_system_id"]) && isset($shipping_metatext_mapping[$product["shipping_system_id"]])) {
                 // 添加直邮关键字
                 $shipping_info = $shipping_metatext_mapping[$product["shipping_system_id"]];
                 $data_transformed[$id]['search_meta_text_custom'] .= 
                     !empty($data_transformed[$id]['search_meta_text_custom']) ? ",".$shipping_info : $shipping_info ;
             }

            if(!empty($product['value'])){
                $data_transformed[$id]['hover_picture'] = $product['value'][0]['value'];
            }

            //tag_id为1和2在前端判断都是等价的，这边只索引一个id
            if(isset($product['tag_id']) && !empty($product['tag_id'])){
                $data_transformed[$id]['tag_id'] = $product['tag_id'];
            }

             $data_transformed[$id]['new_product_flag_int_dynamic'] = ProductPropertyTransform::new_product_transform($product);

             $data_transformed[$id]['sale_forms'] = $product['sale_forms'];

             //$is_yqt = (isset($product['sale_forms']) && $product['sale_forms']=="yqt");

             //如果处于预热阶段,并且不是一起团,sale_forms设置为预热
//            if($product['start_time']>$times && !$is_yqt){
//                $data_transformed[$id]['sale_forms']="pre_hot";
//            }

            $data_transformed[$id]['merchant_id'] =  isset($product['merchant_id'])?$product['merchant_id']:0;
            
            $data_transformed[$id]['is_published_price'] = isset($product['is_published_price'])?$product['is_published_price']:1;

            //线上数据会有area_code为0的情况，所以这边要判断一下。
            if(!empty($product['area_code'])){
                $data_transformed[$id]['area_code'] = $product['area_code'];
            }
            $data_transformed[$id]['countries'] = isset($product['countries'])?$product['countries']:0;
            
            //品牌，分类，功效
            $data_transformed[$id] += ProductPropertyTransform::brand_transform($product);
            $data_transformed[$id] += ProductPropertyTransform::category_transform($product);
             if($product["category"] != "global"){ // TODO 海淘POPDeal暂时屏蔽功效信息,因为目前配置的质量太差
                 $data_transformed[$id] += ProductPropertyTransform::function_transform($product);
             }

            $data_transformed[$id]['is_available_bj'] = 0;
            $data_transformed[$id]['is_available_cd'] = 0;
            $data_transformed[$id]['is_available_sh'] = 0;
            $data_transformed[$id]['is_available_gz'] = 0;
            //库存信息相关
            if(!empty($product['is_available_site'])){
            	foreach ($product['is_available_site'] as $site) {
            		$data_transformed[$id]['is_available_' . $site] = 1;
            	}
            }
            $data_transformed[$id]['is_sellable'] = max($data_transformed[$id]['is_available_bj'],$data_transformed[$id]['is_available_sh'],$data_transformed[$id]['is_available_cd'],$data_transformed[$id]['is_available_gz']);


            //海淘商品组合购
            //
            if(!empty($product['package_id'])){
            	$data_transformed[$id]['package_id'] = $product['package_id'];
            	$data_transformed[$id]['package_name'] = $product['package_name'];
            	$data_transformed[$id]['package_price'] = $product['package_price'];
            }
            
            $data_transformed[$id]['high_priority_sort'] =0;

             //预热
             if($product['start_time']>$times){
                 $data_transformed[$id]['high_priority_sort'] =1;
             }

    	    //不可售
            if($data_transformed[$id]['is_sellable']==0||$product['status']==2||$product['end_time']<$times){
                $data_transformed[$id]['high_priority_sort'] =2;
            }


            //促销信息
             $data_transformed[$id]+= ProductPropertyTransform::promotion_transform($product);
             $data_transformed[$id]+= ProductPropertyTransform::promotion_rule_transform($product);

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

             // 原有销售信息(考虑弃用)
             $data_transformed[$id]['deal_real_buyer_number'] = ($product['real_buyer_number']+1) * 2;
             $data_transformed[$id]['buyer_number'] = $product['buyer_number'];
             $data_transformed[$id]['mall_real_buyer_number'] = $data_transformed[$id]['real_30day_buyer_number'];

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
             if(isset($product['is_new_tag_time']) && !empty($product['is_new_tag_time'])) {
                 // 上新时间
                 $data_transformed[$id]['is_new_tag_time'] = $product['is_new_tag_time'];
             }

             // 商品是否有短视频标记
             $data_transformed[$id]['has_short_video_string_dynamic'] = $product['has_short_video'];
             //商品初始库存
             $data_transformed[$id]['stocks_int_dynamic'] = $product['stocks'];

             //商品对应的店铺id
             if (isset($product['store_id']) && !empty($product['store_id'])) {
                 $data_transformed[$id]['store_id'] = $product['store_id'];
                 foreach($product['store_id'] as $store_id){
                     $data_transformed[$id]["store_id_".$store_id."_string_dynamic"]=$store_id;
                 }
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
//        $file_path = $CONFIG['file']['path']['global_deal'];
//        if (!is_dir($file_path)) {
//            mkdir($file_path, 0777, true);
//        }
//        $time = date("Y-m-d|H:i:s");
//        $file = $file_path . "/" . "global_deal_full_import_" . $time . ".xml";
//        
//        //需要建立data_import目录，然后给写权限
//        require_once (PROJECT_ROOT . '/util/util_write.php');
//        if (!ImportWrite::write_xml_add($data, $file)) {
//            error_log("时间" . $time . "海淘特卖全量索引失败！！！", 3, "/home/logs/data_import/deal_import_error.log");
//        }else{
//            //将本次索引时间记录下来
//            $index_time = file_get_contents($CONFIG['file']['path']['data'] . "/data.properties");
//            $last_time = array();
//            if(!empty($index_time)){
//                $tmp = explode("\n",$index_time);
//                foreach ($tmp as $value) {
//                    $tmp2 = explode("=",$value);
//                    if($tmp2[0] == "global_deal"){
//                        $tmp2[1] = $time;
//                    }
//                    $last_time[$tmp2[0]] = $tmp2[0] . "=" .$tmp2[1];                    
//                }
//            }
//
//            if(!isset($last_time['global_deal'])){
//                $last_time['global_deal'] = "global_deal=".$time;
//            }
//            file_put_contents($CONFIG['file']['path']['data'] . "/data.properties", implode("\n",$last_time));
//        }
    }
    
    public function get_dir_path()
    {
        global $CONFIG;
        $file_path = $CONFIG['file']['path']['global_deal'];
        if (!is_dir($file_path)) {
            mkdir($file_path, 0777, true);
        }
        return $file_path;
    }
}
