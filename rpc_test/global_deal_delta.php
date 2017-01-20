<?php
class GlobalDealDelta
{
    function __construct($job_id) {
        $message = $_POST['message'];
        $message = json_decode(json_decode($message, true) , true);
        $hash_id = $message['hash_id'];
        $operate = $message['operate'];
        $fields = $message['fields'];
                
        //判断是否是海淘
        if (substr($hash_id, 0, 2) == 'ht') {
            $redis = \CRedis::cache('search');
            
            //如果只是因为购买人数发生变化的，中间增量时间要超过30秒，否则这条记录的更新会被抛弃；现在不支持这么快的更新
            if (count($fields) == 2 && in_array('buyer_number', $fields) && in_array('real_buyer_number', $fields)) {
                if ($redis->get("search_delta_" . $hash_id)) {
                    exit();
                }
            }
            
            $flag = 0;
            global $CONFIG;
            if ($operate == 'update') {
            	//对于更新的特卖只看搜素这边关注的字段，不在这些字段里面的不去做索引更新
                foreach ($fields as $field) {
                    if (in_array($field, $CONFIG['field']['global_deal'])) {
                        $flag = 1;
                        break;
                    }
                }
            } 
            else {
                $flag = 1;
            }
            
            if ($flag) {
                
                //最终在处理完的时候，看之前这个job有没有处理过。否则写入数据库，然后redis更新job
                
                //db exec的时候加个锁，如果有锁，则sleep 5秒
                while ($redis->get('search_delta_' . $job_id . '_lock')) {
                    sleep(5);
                }
                
                $redis->set('search_delta_' . $job_id . '_lock', 5, 1);
                
                if (!$redis->get('search_delta_' . $job_id)) {
                    
                    //todo
                    $db = \Db\Connection::instance()->write('search');
                    $operation_datetime = date('Y-m-d H:i:s');
                    $sql = "insert into tuanmei.search_delta_import(operation_datetime,document_id,document_type,operation_type) values('{$operation_datetime}','{$hash_id}','global_deal','update') ON DUPLICATE KEY UPDATE operation_datetime = '{$operation_datetime}', operation_type = 'update'";
                    // $db->exec($sql);
                    error_log(date('Y-m-d H:i:s') . '|' . $sql . "\n", 3,'/home/logs/delta_import.log');
                    
                    //更新redis
                    $redis->set('search_delta_' . $job_id, 1200, 1);
                    $redis->set("search_delta_" . $hash_id, 30,time());
                }
            }
        }
    }
}
