<?php
require_once (PROJECT_ROOT . '/util/JMutil.php');

class ActivityUtil
{
    public static function getActivity($ids,$type){
        require_once (PROJECT_ROOT . '/util/JMutil.php');
        $activity_result=array();
        $result =JMutil::retryClient('Search_Activities', 'getDealAndProductActivityRelation', $ids);
            $activity_result=$result[$type];
        return $activity_result;
    }


    public static function mergeActivity($product,$active,$productStr) {
        if (!empty($product) && !empty($active)) {
            foreach ($product as $k => $v) {
                foreach ($active as $ak => $av) {
                    if(!empty($product[$k][$productStr])) {
                        if ($product[$k][$productStr] == $ak) {
                            $product[$k]['activityId'] = $av;
                            continue;
                        }
                    }
                }
            }
        }
        return $product;
    }

    public static function category_infos_transform($activity, $id, &$trans) {
        for ($i = 1; $i <= 4; ++$i) {
            self::single_category_transform($activity, $id, $i, $trans);   
        }
    }

    public static function function_infos_transform($activity, $id, &$trans) {
        if (!isset($activity['property_decoded']['function'])) {
            return;
        }
        self::calc_ratio_desc($activity['property_decoded']['function'], $activity['product_number']);
        
        $function_ids = array();
        $function_names = array();
        $function_infos = array();
        $index = 1;
        foreach ($activity['property_decoded']['function'] as $idx => $value) {
            if (!self::should_save('function', $value, count($function_ids))) {
                break;
            }
            $function_ids[] = $value['id'];
            $function_names[] = $value['text'];
            $function_infos[] = "{$value['id']},{$value['text']}";
            $trans[$id]["function_name_{$index}_tsvd"] = $value['text'] . "^boost:{$value['ratio']}";
            ++$index;
        }
        $trans[$id]['function_id_imvd'] = $function_ids;
        $trans[$id]['function_name_tmvd'] = $function_names;
        $trans[$id]['function_info_smvd'] = $function_infos;
    }

    /**
     * @param $activity
     * @param $id
     * @param $trans
     */
    public static function brand_infos_transform($activity, $id, &$trans) {
        if (!isset($activity['property_decoded']['brand'])) {
            return;
        }
        self::calc_ratio_desc($activity['property_decoded']['brand'], $activity['product_number']);
        
        $brand_ids = array();
        $brand_names = array();
        $brand_infos = array();
        $index = 1;
        foreach ($activity['property_decoded']['brand'] as $idx => $value) {
            if (!self::should_save('brand', $value, count($brand_ids))) {
                break;
            }
            $brand_ids[] = $value['id'];
            $brand_names[] = $value['text'];
            $brand_infos[] = "{$value['id']},{$value['text']}";
            $trans[$id]["brand_name_{$index}_tsvd"] = $value['text'] . "^boost:{$value['ratio']}";
            ++$index;
        }
        $trans[$id]['brand_id_imvd'] = $brand_ids;
        $trans[$id]['brand_name_tmvd'] = $brand_names;
        $trans[$id]['brand_info_smvd'] = $brand_infos;
    }

    /**
     * @param $activity
     * @param $id
     * @param $sub_cat
     * @param $trans
     */
    private static function single_category_transform($activity, $id, $sub_cat, &$trans) {
        $real_cat = "category_v3_{$sub_cat}";
        if (!isset($activity['property_decoded'][$real_cat])) {
            return;
        }
        self::calc_ratio_desc($activity['property_decoded'][$real_cat], $activity['product_number']);

        $cat_ids = array();
        $cat_names = array();
        $cat_infos = array();
        $index = 1;
        foreach ($activity['property_decoded'][$real_cat] as $idx => $value) {
            if (!self::should_save($real_cat, $value, count($cat_ids))) {
                break;
            }
            $cat_ids[] = $value['id'];
            $cat_names[] = $value['text'];
            $cat_infos[] = "{$value['id']},{$value['text']}";
            $trans[$id]["category_name_{$sub_cat}_{$index}_tsvd"] = $value['text'] . "^boost:{$value['ratio']}";
            ++$index;
        }
        $trans[$id]["category_id_{$sub_cat}_imvd"] = $cat_ids;
        $trans[$id]["category_name_{$sub_cat}_tmvd"] = $cat_names;
        $trans[$id]["category_info_{$sub_cat}_smvd"] = $cat_infos;
    }

    private static function attr_comp_desc($lhs, $rhs) {
        if ($lhs['product_count'] > $rhs['product_count']) {
            return -1;
        } else if ($lhs['product_count'] < $rhs['product_count']) {
            return 1;
        } else {
            return 0;
        }
    }
    
    private static function calc_ratio_desc(&$attrs, $prod_num) {
        $prod_num = isset($prod_num) ? max($prod_num,1) : 1;
        foreach ($attrs as $idx => $value) {
            $attrs[$idx]['ratio'] = $value['product_count'] * 1.0 / $prod_num;
        }
        // 按count降序排列
        usort($attrs, "self::attr_comp_desc");
    }

    private static function should_save($sub_cat, $attr_value, $saved_num) {
        global $CONFIG;
        static $ATTR_CONF = array(
            // 一级分类
            "category_v3_1" => array("prod_min" => 10, "ratio_min" => 0.5, "limit" => 1),
            // 二级分类
            "category_v3_2" => array("prod_min" => 10, "ratio_min" => 0.3, "limit" => 3),
            // 三级分类
            "category_v3_3" => array("prod_min" => 10, "ratio_min" => 0.1, "limit" => 10),
            // 四级分类
            "category_v3_4" => array("prod_min" => 30, "ratio_min" => 0.05, "limit" => 10),
            // 品牌
            "brand" => array("prod_min" => 10, "ratio_min" => 0.3, "limit" => 3),
            // 功效
            "function" => array("prod_min" => 10, "ratio_min" => 0.2, "limit" => 5)
        );
        if (isset($CONFIG['activity_attr']) && is_array($CONFIG['activity_attr'])) {
            $ATTR_CONF = $CONFIG['activity_attr'];
        }

        $conf = $ATTR_CONF[$sub_cat];
        return ($saved_num < $conf['limit']) && ($attr_value['product_count'] >= $conf['prod_min']) &&
            ($attr_value['ratio'] > $conf['ratio_min']);
    }
}
