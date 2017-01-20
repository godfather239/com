<?php
class ProductPropertyTransform
{
    static public function brand_transform($data) {
        return array(
            'brand_id' => $data['brand_id']!=null?$data['brand_id']:0,
            'brand_label' => $data['brand_label']!=null?$data['brand_label']:0,
            'is_authorization' => !empty($data['is_authorization'])?$data['is_authorization']:0,
            'brand_chinese_name' => $data['chinese_name']!=null? $data['chinese_name']:" ",
            'brand_pinyin_name' => $data['pinyin_name']!=null?$data['pinyin_name']:" ",
            'brand_english_name' => $data['english_name']!=null?$data['english_name']:" ",
            'brand_info' => $data['brand_id'] . "," . $data['chinese_name'] . "," . $data['english_name'] . "," . $data['pinyin_name'],
        );
    }

    static public function category_transform($data) {
        return array(
            'category_id_1' => $data['category_v3_1']!=null?$data['category_v3_1']:0,
            'category_name_1' => $data['categorys']['category_v3_1'],
            'category_info_1' => $data['category_v3_1'] . ',' . $data['categorys']['category_v3_1'],
            
            'category_id_2' => $data['category_v3_2']!=null?$data['category_v3_2']:0,
            'category_name_2' => $data['categorys']['category_v3_2'],
            'category_info_2' => $data['category_v3_2'] . ',' . $data['categorys']['category_v3_2'],
            
            'category_id_3' => $data['category_v3_3']!=null?$data['category_v3_3']:0,
            'category_name_3' => $data['categorys']['category_v3_3'],
            'category_info_3' => $data['category_v3_3'] . ',' . $data['categorys']['category_v3_3'],
            
            'category_id_4' => $data['category_v3_4']!=null?$data['category_v3_4']:0,
            'category_name_4' => $data['categorys']['category_v3_4'],
            'category_info_4' => $data['category_v3_4'] . ',' . $data['categorys']['category_v3_4'],
        );
    }
    
    static public function function_transform($data) {
        return array(
            'function_id' => array_keys($data['functions']) ,
            'function_name' => array_values($data['functions']) ,
            'function_info' => self::key_value_join($data['functions']) ,
        );
    }

    static public function promotion_transform($data){
        $return = array();
        $sites = array('bj','sh','cd','gz');
        $promotion_site_ids_total = array();
        foreach ($sites as $site) {
            if(isset($data['promotion_'.$site])){
                $return['promotion_'.$site] = json_encode($data['promotion_' . $site]);
                $promotion_site_ids = array();
                foreach($data['promotion_' . $site] as $promotion_rule){
                    $promotion_site_ids[] = $promotion_rule['rule_id'];
                    $promotion_site_ids_total[] = $promotion_rule['rule_id'];
                }
                // Ticket 101469: 增加结构化促销属性
                // promotion_cd_rule_ids,promotion_bj_rule_ids,promotion_gz_rule_ids,promotion_sh_rule_ids
                $return['promotion_'.$site.'_rule_ids_mv_text_dynamic'] = array_unique($promotion_site_ids);
            }
        }
        // 所有促销规则的汇总集合,无视分站
        $return['promotion_rule_ids_mv_text_dynamic'] = array_unique($promotion_site_ids_total);
        return $return;
    }

    /**
     * @param $data
     * @brief  为了实现系列-店铺的完整对应，使用多个dynamic field存储
     * @return series info
     */
    static public function series_transform($data) {
        $return = array();
        if (isset($data['store_id']) && !empty($data['store_id']) && 
            isset($data['series_info_orig']) && !empty($data['series_info_orig'])) {
            $siorig = $data['series_info_orig'];
            $series_id_all = array();
            $series_name_all = array();
            $series_info_all = array();
            foreach ($data['store_id'] as $store_id) {
                if (isset($siorig[$store_id]) && !empty($siorig[$store_id])) {
                    $ids = array();
                    $names = array();
                    $infos = array();
                    foreach ($siorig[$store_id] as $series_id => $value) {
                        $ids[] = $series_id;
                        $names[] = $value['series_name'];
                        $infos[] = $series_id . ',' . $value['series_name'];
                        $series_id_all[] = $series_id;
                        $series_name_all[] = $value['series_name'];
                        $series_info_all[] = $series_id . ',' . $value['series_name'];
                    }
                    $return["store_{$store_id}_series_id_int_mv_dynamic"] = $ids;
                    $return["store_{$store_id}_series_name_mv_text_dynamic"] = $names;
                    $return["store_{$store_id}_series_info_string_mv_dynamic"] = $infos;
                }
            }
            $return['series_id'] = $series_id_all;
            $return['series_name'] = $series_name_all;
            $return['series_info'] = $series_info_all;
        }
        return $return;
    }
    
    private function key_value_join($array) {
        $return = array();
        if (!empty($array)) {
            foreach ($array as $key => $value) {
                $return[] = $key . "," . $value;
            }
        }
        return $return;
    }

    /**
     * @param $product
     * @brief  促销规则拼接(促销规则类型和促销规则描述)
     * @return promotion rule
     */
    static public function promotion_rule_transform($product){
        $return = array();
        //促销规则类型
        if (isset($product['promotion_rule_type']) && !empty($product['promotion_rule_type'])){
            $return['promotion_rule_info_mv_text_dynamic'] = array_unique(array_merge($return,$product['promotion_rule_type']));
        }
        //促销规则描述
        if (isset($product['promotion_rule_description']) && !empty($product['promotion_rule_description'])) {
            $return['promotion_rule_info_mv_text_dynamic'] = array_unique(array_merge($return['promotion_rule_info_mv_text_dynamic'],$product['promotion_rule_description']));
        }
        return $return;
    }

    /**
     * @param $product
     * @brief 价格显示逻辑：对于jumei_price字段,优先取不含税的价格（value_of_goods）,
     * 如果此字段(value_of_goods)为空或者为0,则取jumei_price
     * 设置赋值开关$use_value_of_goods
     * @return jumei_price
     */
    static public function jumei_price_transform($product,$doc_type) {
        global $CONFIG;
        //开关为true
        $use_value_of_goods = $CONFIG['jumei_price_assigned']['use_value_of_goods'];
        $value_of_goods_valid = ($use_value_of_goods && isset($product['value_of_goods'])
            && is_numeric($product['value_of_goods']) && $product['value_of_goods'] != 0);
        if($doc_type == "mall_product" || $doc_type == "pop_mall"){
            return $value_of_goods_valid ? $product['value_of_goods'] : $product['mall_price'];
        }
        if($doc_type == "deal" || $doc_type == "pop"){
            return $value_of_goods_valid ? $product['value_of_goods'] : $product['discounted_price'];
        }
        if($doc_type == "global_deal" || $doc_type == "global_mall" || $doc_type == "global_pop_mall"){
            return $value_of_goods_valid ? $product['value_of_goods'] : $product['sku_min_price'];
        }
    }

    static public function new_product_transform($product){
        $new_product_flag = 0;
        if(isset($product['tag_id']) && !empty($product['tag_id']) && in_array(4,$product['tag_id']) ){
            $new_product_flag = 4;
        }
        return $new_product_flag;
    }
    
    static public function relation_deal_transform($product, $id, &$data_transformed) {
        if (isset($product['has_relation_deal']) && $product['has_relation_deal']==1) {
            $data_transformed[$id]['relation_tags_int_mv_dynamic'] = array(22);//双11活动标签
        }

        if (isset($product['relation_deal_discounted_price']) && is_numeric($product['relation_deal_discounted_price'])) {
            $data_transformed[$id]['relation_deal_discounted_price_float_dynamic'] = $product['relation_deal_discounted_price'];
        }

        if (isset($product['relation_deal_hash_id']) && !empty($product['relation_deal_hash_id'])) {
            $data_transformed[$id]['relation_deal_hash_id_string_dynamic'] = $product['relation_deal_hash_id'];
        }
    }

    static public function sale_status_transform($product, $id, &$data_transformed){
        if(!isset($product['start_time']) || !isset($product['end_time'])||  empty($product['start_time']) || empty($product['end_time'])){
            $data_transformed[$id]['sale_status_int_dynamic'] = 1;
            return;
        }
        $times = time();
        if($product['start_time'] > $times){
            $data_transformed[$id]['sale_status_int_dynamic'] = 0;
        }elseif($product['start_time']<$times && $times<$product['end_time']){
            $data_transformed[$id]['sale_status_int_dynamic'] = 1;
        }elseif($times>$product['end_time']){
            $data_transformed[$id]['sale_status_int_dynamic'] = 2;
        }
    }

    static public function sort_field_transform($product, $id, &$data_transformed){
        if(isset($product['tag_id']) && in_array(24,$product['tag_id'])){
            $data_transformed[$id]['sort_field_int_dynamic'] = 1;
        }else{
            $data_transformed[$id]['sort_field_int_dynamic'] = 0;
        }
    }

    /**
     * 处理调性图、品牌调性相关属性
     * @param $product
     * @param $id
     * @param $data_transformed
     */
    static public function dx_img_transfrom($product, $id, &$data_transformed){
        // 调性图相关属性
        // 品牌调性
        if (ProductPropertyTransform::is_valid($product,'brand_dx')) {
            $data_transformed[$id]["brand_dx_int_dynamic"] = $product['brand_dx'];
        }
        // 是否开启调性图
        if (ProductPropertyTransform::is_valid($product,'is_deal_dx_enable')) {
            $data_transformed[$id]["is_deal_dx_enable_int_dynamic"] = $product['is_deal_dx_enable'];
        }
        // 是否有新图
        if (ProductPropertyTransform::is_valid($product,'with_new_image')) {
            $data_transformed[$id]["with_new_image_int_dynamic"] = $product['with_new_image'];
        }
        // 调性图数组
        if (ProductPropertyTransform::is_valid($product,'dx_img')) {
            $data_transformed[$id]["dx_img_string_dynamic"] = json_encode($product['dx_img']);
        }
    }

    static public function is_valid($obj, $field){
        return isset($obj) && isset($obj[$field]) && ($obj[$field] === '0' || $obj[$field] === 0 || !empty($obj[$field]));
    }
}
