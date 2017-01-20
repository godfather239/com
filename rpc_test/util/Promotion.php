<?php

/**
 *
 */
class Promotion {
    
    public static function getPromotion($ids, $booth) {
        global $CONFIG;
        require_once (PROJECT_ROOT . '/util/JMutil.php');

        if (empty($ids)) {
            return false;
        }
        
    	$promotion = array();
        $is_mall =  ($booth == "mall" || $booth == "mediamall" || $booth == "globalmallmedia" || $booth == "globalmall");
        // 如果是mall则使用product_id, 否则使用hash_id
        $type = $is_mall ? "product_id" : "deal_hash_id";
        $params = array();
        foreach ($ids as $value) {
            $params[] = array(
                'booth' => $booth,
                $type => $value
            );
        }
        $ids_json = json_encode(array(
            'stage' => $CONFIG["promotion_effect_env"],
            'effectiveTimeRange' => 2, // 查询当前生效和未来生效的规则
            'products' => $params
        ));
        $promotion_rule = json_decode(JMutil::getThriftClient('PromotionFaceService', 'queryPromotionRule', array(
            $ids_json
        )) , true);

        if (!empty($promotion_rule['body'])) {
            foreach ($promotion_rule['body'] as $value) {
                foreach ($value['rules'] as $rule) {
                    $tmp = array(
                        'site' => $rule['site'],
                        'rule_id' => $rule['rule_id'],
                        'description' => $rule['description'],
                        'type'=>$rule['type'],
                        'start_time' => $rule['start_time'],
                        'end_time' => $rule['end_time'],
                        'rule_item_id' => $rule['rule_item_id']
                    );
                    
                    switch ($rule['site']) {
                        case 'all':
                            $promotion[$value[$type]]['promotion_bj'][] = $promotion[$value[$type]]['promotion_sh'][] = $promotion[$value[$type]]['promotion_cd'][] = $promotion[$value[$type]]['promotion_gz'][] = $tmp;
                            break;
                        case 'bj':
                            $promotion[$value[$type]]['promotion_bj'][] = $tmp;
                            break;
                        case 'sh':
                            $promotion[$value[$type]]['promotion_sh'][] = $tmp;
                            break;
                        case 'cd':
                            $promotion[$value[$type]]['promotion_cd'][] = $tmp;
                            break;
                        case 'gz':
                            $promotion[$value[$type]]['promotion_gz'][] = $tmp;
                            break;
                        default:
                            break;
                    }

                    /**
                     * 按促销类型搜索商品(用户搜索满减、满返、满赠等关键字时，能够按促销规则类型召回)
                     * 防止促销规则不合理的描述造成误搜索，加一个对规则描述是否拼接到类型中的开关
                     * 搜索“满减”“满赠”“换购”等不返回未来生效商品
                     */
                    $alreadyStart = (isset($rule['start_time']) && !empty($rule['start_time']) && $rule['start_time'] <= time()) ? true : false;
                    if($alreadyStart && array_key_exists($rule['type'],$CONFIG['promotion_rules']['info'])){
                        if(isset($CONFIG['promotion_rules']['info'][$rule['type']])
                            && !empty($CONFIG['promotion_rules']['info'][$rule['type']])){
                            $promotion[$value[$type]]['promotion_rule_type'][] = $CONFIG['promotion_rules']['info'][$rule['type']];
                        }
                        //拼接描述开关为true
                        if ($CONFIG['promotion_rules']['add_description']) {
                            $promotion[$value[$type]]['promotion_rule_description'][] = $rule['description'];
                        }
                    }
                }
            }
        }

        return $promotion;
    }

    /**
     * @param $ids string 规则ID字符串
     * @return mixed 规则详情数组
     */
    public static function getPromotionByRuleId($ids){
        require_once (PROJECT_ROOT . '/util/JMutil.php');
        $result =  json_decode(JMutil::getThriftClient('PromotionFaceService', 'getRuleDetails', array($ids)),true);
        if ($result["status"] == 200 && !empty($result['body'])) {
            return $result["body"];
        }
        return false;
    }
}
