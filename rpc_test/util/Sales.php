<?php
include(PROJECT_ROOT . "/util/CRedis.php");
/**
 *
 */
class Sales
{
    /**
     * 分别获取商品7天和30天的销售数据
     * @param $product_ids
     * @return array
     */
    public static function getSales($product_ids) {
    	require_once (PROJECT_ROOT . '/util/JMutil.php');
        $sales = array();
        $redis = \CRedis::storage('promocard');

        if(!empty($product_ids)){
            foreach ($product_ids as $product_id) {
                $key = 'productCrmCommodityCalculateMonth'.$product_id;
                $volume_30day = $redis->hget($key, 'sum');
                $sales[$product_id]['real_30day_mall_sale_volume'] = empty($volume_30day) ? 0 : $volume_30day;
            }
        }
        return $sales;
    }

}
