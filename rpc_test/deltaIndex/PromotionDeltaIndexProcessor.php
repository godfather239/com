<?php
/**
 * Created by IntelliJ IDEA.
 * User: shizhongl
 * Date: 8/9/16
 * Time: 10:40 AM
 */
define('PROJECT_ROOT', realpath(dirname(__FILE__)."/../"));
date_default_timezone_set('Asia/Shanghai');
if (file_exists(PROJECT_ROOT . "/includes/config.php")) {
    include (PROJECT_ROOT . "/includes/config.php");
} else {
    include (PROJECT_ROOT . "/includes/config.online.php");
}

//  公用类库自动初始化
require_once(PROJECT_ROOT . '/Vendor/Bootstrap/Autoloader.php');
Bootstrap\Autoloader::instance()->init();
require_once(PROJECT_ROOT . '/util/util_write.php');
require_once(PROJECT_ROOT . '/util/SolrUtil.php');
require_once(PROJECT_ROOT . '/util/DeltaIndexUtil.php');
require_once(PROJECT_ROOT . '/util/Promotion.php');
require_once(PROJECT_ROOT . '/util/PromotionHelper.php');

global $CONFIG;
$ip = $CONFIG['server']['redis_ip'];
$port = $CONFIG['server']['redis_port'];
$solr_master = "http://{$CONFIG['server']['solr_master']}/select";
$redis = new Redis();
$date = date("Ymd");

$consumed_message = "";
if($redis->pconnect($ip, $port, 1)) {
    $promotion_id = $redis->sPop("promotion_change_queue");
    while(isset($promotion_id) && !empty($promotion_id)){
        $ids = '[' . $promotion_id . ']';
        $promotionDetails = Promotion::getPromotionByRuleId($ids);
        if(empty($promotionDetails) || sizeof($promotionDetails) < 1){
            file_put_contents("/home/logs/delta_import/promotion_delta_{$date}.log", date("Y-m-d|H:i:s")." promotion consumed failed, empty promtion detail: {$promotion_id} \n",FILE_APPEND);
            return;
        }
        //　传入单个id,所以固定只取每一条
        $promotionDetail = $promotionDetails[0];

        // 拿出条件维度,通过搜索检索出所有已有的商品ID
        $promotionHelper = new PromotionHelper($promotionDetail);

        // 将促销规则之前能命中的商品ID插入待重建队列(重建老数据)
        $products = $promotionHelper->fetchOriginalEffectProducts();
        while(!empty($products)){
            DeltaIndexUtil::addProductToDeltaQueue($redis, $products);
            $products = $promotionHelper->fetchOriginalEffectProducts();
        }
        // 将促销规则当前能命中的商品ID插入待重建队列(处理促销规则新增product_id/brand_id的场景)
        $products = $promotionHelper->fetchCurrentEffectedProducts();
        // var_dump($products);
        while(!empty($products)){
            DeltaIndexUtil::addProductToDeltaQueue($redis, $products);
            $products = $promotionHelper->fetchCurrentEffectedProducts();
        }
        $consumed_message .= "${promotion_id},";
        $promotion_id = $redis->sPop("promotion_change_queue"); // 循环处理下一个促销规则变更
    }

    file_put_contents("/home/logs/delta_import/promotion_delta_{$date}.log", date("Y-m-d|H:i:s")." promotion consumed: {$consumed_message} \n",FILE_APPEND);
}