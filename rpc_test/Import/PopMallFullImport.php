<?php
require_once (PROJECT_ROOT . "/Import/load_source_data.php");
require_once (PROJECT_ROOT . '/util/JMutil.php');
require_once (PROJECT_ROOT . '/util/util_write.php');
class PopMallFullImport implements LoadSourceData
{
    public function getIds($flag,$num=50){
        $hash_ids =JMutil::retryClient('JumeiProduct_Search_Read_Deals', 'getPOPMallProductIds', array($flag,$num));
        ImportWrite::log_info("type:pop_mall,flag:{$flag},num:{$num},ids:{$hash_ids}");
        return $hash_ids;
    }

    public function getDetails($ids){
        $ids=array_filter($ids);
        if(count($ids)>0){
        $result =JMutil::retryClient('JumeiProduct_Search_Read_Deals', 'getPopMallDetailForSearch', array($ids));
        return $result;
        }
        else return null;
    }

    public function detail2Product($pop_mall_detail,$result,$ids){
        $result=html_entity_decode($result);
        $product = json_decode($result,true);
        if(!empty($product)) {
            // 促销规则
            require_once(PROJECT_ROOT . '/util/Promotion.php');
            $product = JMutil::two_dimensional_array_merge(Promotion::getPromotion($ids, "mediamall"), $product);
            // 专场信息
            require_once (PROJECT_ROOT . '/util/ActivityUtil.php');
            $queryId=array(array(),$ids,array());
            $product=ActivityUtil::mergeActivity($product,ActivityUtil::getActivity($queryId,"partnerMall"),"product_id");
            //获取系列
            require_once(PROJECT_ROOT . '/util/Series.php');
            $params = JMutil::array_productId_storeId($product);
            $product = JMutil::array_merge_for_series(SeriesTransform::getSeries($params), $product);

            // 销售信息
            // require_once (PROJECT_ROOT . '/util/Sales.php');
            // $product = JMutil::mergeByIndenity($product, Sales::getSales($ids) ,"product_id");

            $pop_mall_detail += $this->field_transform($product);
        }
        return $pop_mall_detail;
    }

    public function read_original_data() {
        require_once (PROJECT_ROOT . '/util/util_write.php');
        $file_path = $this->get_dir_path();

        $tmp_file = "{$file_path}/tmp.xml";
        $fp = fopen($tmp_file, "w");
        if (!$fp) {
            error_log("时间" . date("Y-m-d|H:i:s") . "Failed to open file {$tmp_file}", 3, "/home/logs/data_import/pop_mall_import_error.log");
            return array();
        }
        fwrite($fp, "<add>");

        $flag=0;
        while($flag!=-1){
            $hash_ids =$this->getIds($flag);
            if($hash_ids=="[[],[]]"){
                $flag=-1;
            }else{
                $pop_mall_detail = array();
                $hash_ids_arrays = json_decode($hash_ids,true);
                if(!empty($hash_ids_arrays)) {
                    $flag = $hash_ids_arrays[0]['minId'];
                    $ids = $hash_ids_arrays[1];
                    $result =$this->getDetails($ids);
                    $pop_mall_detail=$this->detail2Product($pop_mall_detail,$result,$ids);
                    $xml = ImportWrite::get_xml_body($pop_mall_detail);
                    if (!empty($xml)) {
                        fwrite($fp, $xml);
                    }
                }
            }
        }
        fwrite($fp, "</add>");
        fclose($fp);
        $time = date("Y-m-d|H:i:s");
        $file = $file_path . "/" . "pop_mall_full_import_" . $time . ".xml";
        rename($tmp_file, $file);
        ImportWrite::update_timestamp('pop_mall', $time);
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
            $data_transformed[$id]['doc_id'] = 'pop_mall_' . $product['product_id'];
            $data_transformed[$id]['doc_type'] = 'pop_mall';
            $data_transformed[$id]['product_id'] = $product['product_id'];
            $data_transformed[$id]['unique_id'] = $product['product_id'];
            $data_transformed[$id]['mall_product_name'] = $product['mall_product_name'];
            $data_transformed[$id]['display_name_highlight'] = $product['mall_product_name'];
            
            $data_transformed[$id]['market_price'] = $product['market_price'];
            $data_transformed[$id]['jumei_price'] = ProductPropertyTransform::jumei_price_transform($product,"pop_mall");
            $data_transformed[$id]['sort_price'] = $data_transformed[$id]['jumei_price'];
            $data_transformed[$id]['discount'] = $product['discount'];
            $data_transformed[$id]['sort_start_date'] = $product['start_time'];
            
            $data_transformed[$id]['show_status'] = $product['show_status'];
            
            if (!empty($product['sale_price']) && !empty($product['sale_end_time'])) {
                $data_transformed[$id]['sale_price'] = $product['sale_price'];
                $data_transformed[$id]['sale_start_time'] = $product['sale_start_time'];
                $data_transformed[$id]['sale_end_time'] = $product['sale_end_time'];
            }

            //这个字段商品库提供，未建入索引
            //$data_transformed[$id]['7days_buyer_number']= $product['7days_buyer_number'];
            //口碑信息相关isset
            $data_transformed[$id]['product_reports_number'] = ($product['product_reports_number'])?$product['product_reports_number']:0;
            $data_transformed[$id]['sort_popularity'] = ($product['product_reports_number'])?$product['product_reports_number']:0;
            $data_transformed[$id]['product_report_rating'] = $product['product_report_rating'];
            $data_transformed[$id]['deal_comments_number'] = $product['deal_comments_number'];
            $data_transformed[$id]['refund_policy'] = $product['refund_policy'];
            
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

            //tag_id为1和2在前端判断都是等价的，这边只索引一个id
            if (isset($product['tag_id']) && !empty($product['tag_id'])) {
                $data_transformed[$id]['tag_id'] = $product['tag_id'];
            }

            $data_transformed[$id]['new_product_flag_int_dynamic'] = ProductPropertyTransform::new_product_transform($product);

            //品牌，分类，功效
            $data_transformed[$id]+= ProductPropertyTransform::brand_transform($product);
            $data_transformed[$id]+= ProductPropertyTransform::category_transform($product);
           
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
            
            
            //促销信息
            $data_transformed[$id]+= ProductPropertyTransform::promotion_transform($product);
            $data_transformed[$id]+= ProductPropertyTransform::promotion_rule_transform($product);
            
            $data_transformed[$id]['countries'] = isset($product['countries'])?$product['countries']:0;
            $data_transformed[$id]['merchant_id'] = isset($product['merchant_id'])?$product['merchant_id']:0;
            $data_transformed[$id]['high_priority_sort'] =0;
            if($data_transformed[$id]['is_sellable']==0||$product['show_status']==5){
                $data_transformed[$id]['high_priority_sort'] =2;
            }
            if (isset($product['activityId']) && !empty($product['activityId'])) {
                $data_transformed[$id]['activityId'] = $product['activityId'];
            }
            $data_transformed[$id]['deal_sort'] =1;

            //$data_transformed[$id]['real_30day_mall_sale_volume'] = isset($product['real_30day_mall_sale_volume']) ? $product['real_30day_mall_sale_volume'] : 0;
            //$data_transformed[$id]['real_30day_deal_sale_volume'] = isset($product['real_30day_deal_sale_volume']) ? $product['real_30day_deal_sale_volume'] : 0;

            // 记录30天销售信息
            $data_transformed[$id]['real_30day_buyer_number'] = isset($product['real_30day_buyer_number']) ? $product['real_30day_buyer_number'] : 0;
            $data_transformed[$id]['fake_30day_buyer_number'] = isset($product['fake_30day_buyer_number']) ? $product['fake_30day_buyer_number'] : 0;
            $data_transformed[$id]['real_30day_sales_amount'] = isset($product['real_30day_sales_amount']) ? $product['real_30day_sales_amount'] : 0;

            // 销售排序信息
            $data_transformed[$id]['sort_sales_volume'] = $data_transformed[$id]['real_30day_buyer_number'];
            $data_transformed[$id]['sort_sales_amount'] = $data_transformed[$id]['real_30day_sales_amount'];

            // 原有信息相关数据(考虑弃用)
            $data_transformed[$id]['buyer_number'] = $product['real_30day_buyer_number'];
            $data_transformed[$id]['sale_amount']= $product['7days_sale_amount'];
            $data_transformed[$id]['mall_real_buyer_number'] = $data_transformed[$id]['real_30day_buyer_number'];

            $data_transformed[$id]['shipping_system_id'] = $product['shipping_system_id'];

            // 增加地区直邮策略搜索关键字,如香港直邮,澳门直邮等关键字
            $shipping_metatext_mapping = $CONFIG['shipping_system_metatext'];
            if(isset($product["shipping_system_id"]) && isset($shipping_metatext_mapping[$product["shipping_system_id"]])) {
                // 添加直邮关键字
                $shipping_info = $shipping_metatext_mapping[$product["shipping_system_id"]];
                $data_transformed[$id]['search_meta_text_custom'] .=
                    !empty($data_transformed[$id]['search_meta_text_custom']) ? ",".$shipping_info : $shipping_info ;
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

            if (isset($product['shipping_system_id']) && is_numeric($product['shipping_system_id'])) {
                $data_transformed[$id]['shipping_system_id'] = $product['shipping_system_id'];
            }
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
    	//写文件的时候，文件名是不一样的；
//        global $CONFIG;
//        $file_path = $CONFIG['file']['path']['pop_mall'];
//        if (!is_dir($file_path)) {
//            mkdir($file_path, 0777, true);
//        }
//        $time = date("Y-m-d|H:i:s");
//        $file = $file_path . "/" . "pop_mall_full_import_" . $time . ".xml";
//        
//        //需要建立data_import目录，然后给写权限
//        require_once (PROJECT_ROOT . '/util/util_write.php');
//        if (!ImportWrite::write_xml_add($data, $file)) {
//            error_log("时间" . $time . "pop_mall全量索引失败！！！", 3, "/home/logs/data_import/pop_mall_import_error.log");
//        }else{
//            //将本次索引时间记录下来
//            $index_time = file_get_contents($CONFIG['file']['path']['data'] . "/data.properties");
//            $last_time = array();
//            if(!empty($index_time)){
//                $tmp = explode("\n",$index_time);
//                foreach ($tmp as $value) {
//                    $tmp2 = explode("=",$value);
//                    if($tmp2[0] == "pop_mall"){
//                        $tmp2[1] = $time;
//                    }
//                    $last_time[$tmp2[0]] = $tmp2[0] . "=" .$tmp2[1];                    
//                }
//            }
//
//            if(!isset($last_time['pop_mall'])){
//                $last_time['pop_mall'] = "pop_mall=".$time;
//            }
//            file_put_contents($CONFIG['file']['path']['data'] . "/data.properties", implode("\n",$last_time));
//        }
    }
    
    public function get_dir_path()
    {
        global $CONFIG;
        $file_path = $CONFIG['file']['path']['pop_mall'];
        if (!is_dir($file_path)) {
            mkdir($file_path, 0777, true);
        }
        return $file_path;
    }
}