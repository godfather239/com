<?php
class SkuInventoryDelta
{
    function __construct($job_id) {
        $message = $_POST['message'];
        $message = json_decode($message, true);
        $sku_no = $message['sku'];
        
        require_once (PROJECT_ROOT . '/util/JMutil.php');
        $product = JMutil::getThriftClient('JumeiProduct_Read_Thrift_Product', 'getDealProductBySku', array(
            $sku_no
        ));
                
        //先判断海淘的特卖
        
        if (isset($product['global_deal']) && !empty($product['global_deal'])) {
            
            $redis = \CRedis::cache('search');
            
            while ($redis->get('search_delta_' . $job_id . '_lock')) {
                sleep(5);
            }
            $redis->set('search_delta_' . $job_id . '_lock', 5, 1);
            if (!$redis->get('search_delta_' . $job_id)) {
            	//DB INIT
            	$db = \Db\Connection::instance()->write('search');

                foreach ($product['global_deal'] as $deal_id) {
                
				$operation_datetime = date('Y-m-d H:i:s');
	  
                    //insert
                    $sql = "insert into search.search_delta_import(operation_datetime,document_id,document_type,operation_type) values('{$operation_datetime}','{$deal_id}','global_deal','update') ON DUPLICATE KEY UPDATE operation_datetime = '{$operation_datetime}', operation_type = 'update'";
                    $db->exec($sql);
                    error_log(date('Y-m-d H:i:s') . '|' . $sql . "\n", 3, '/home/logs/delta_import.log');

                }
                $redis->set('search_delta_' . $job_id, 1200, 1);
            }
        }
    }
}
