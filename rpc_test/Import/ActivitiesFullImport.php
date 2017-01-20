<?php

require_once (PROJECT_ROOT . '/util/JMutil.php');
require_once (PROJECT_ROOT . '/util/ActivityUtil.php');

class ActivitiesFullImport
{
    public function getActivitiesData($page){
        $result =JMutil::retryClient('Search_Activities', 'getActivityListByPage', array($page));
        return $result;
    }

    public function setDetailValue($activities_full){
        $page=1;
        $detail=array("init value");
        while(true){
            $detail=$this->getActivitiesData($page);
            if(!empty($detail)  && isset($detail[0]['isEnd']) && $detail[0]['isEnd']=='1'){
                break;
            }
            if(!empty($detail)){
                $activities_full=array_merge($activities_full,$detail);
                //$activities_full+=$detail;
            }
            $page++;
        }

        return $activities_full;
    }

    public function read_original_data()
    {
        $activities_full=array();
        $activities_full=$this->setDetailValue($activities_full);
        return $activities_full;
    }

    /**
     * 索引的字段是根据前端的业务需求
     */
    public function field_transform($data)
    {
        $data_transformed = array();
        foreach ($data as $id => $activity) {
            $data_transformed[$id]['id'] = $activity['id'];
//            $data_transformed[$id]['brand_id'] = (!empty($activity['brand_id']))?$activity['brand_id']:0;
            $data_transformed[$id]['sales_volume_mobile_pay'] = isset($activity['sales_volume_mobile_pay'])?$activity['sales_volume_mobile_pay']:0;
            if (isset($activity['platform'])) {
                $data_transformed[$id]['platform'] = $activity['platform'];
            }
            if (isset($activity['symbol'])) {
                $data_transformed[$id]['symbol'] = $activity['symbol'];
            }
            if (isset($activity['index_main_title'])) {
                $data_transformed[$id]['index_main_title'] = $activity['index_main_title'];
            }
            if (isset($activity['name'])) {
                $data_transformed[$id]['name'] = $activity['name'];
            }
            if (isset($activity['sale_promotion_word'])) {
                $data_transformed[$id]['sale_promotion_word'] = $activity['sale_promotion_word'];
            }
            if (isset($activity['visiting_index'])) {
                $data_transformed[$id]['visiting_index'] = $activity['visiting_index'];
            }
            if (isset($activity['preheatting_time'])) {
                $data_transformed[$id]['preheatting_time'] = $activity['preheatting_time'];
            }
            if (isset($activity['start_time'])) {
                $data_transformed[$id]['start_time'] = $activity['start_time'];
            }
            if (isset($activity['end_time'])) {
                $data_transformed[$id]['end_time'] = $activity['end_time'];
            }
            if (isset($activity['search_entrance_image']) && !empty($activity['search_entrance_image'])) {
                $data_transformed[$id]['search_entrance_image'] = $activity['search_entrance_image'];
            }
            if (isset($activity['url']) && !empty($activity['url'])) {
                $data_transformed[$id]['url'] = $activity['url'];
            }
            $high_priority_sort = 2;   // 默认值
            $now = microtime(true);
            if (isset($activity['preheatting_time']) && $activity['preheatting_time']<=$now &&
                    $activity['start_time']>$now) {
                $high_priority_sort = 1;   // 预热为1
            } else if ($activity['start_time']<$now && $activity['end_time']>$now) {
                $high_priority_sort = 0;   // 在售为0
            }
            $data_transformed[$id]['high_priority_sort'] = $high_priority_sort;

            $activity['property_decoded'] = isset($activity['property']) ? json_decode($activity['property'], true) : array();
            ActivityUtil::brand_infos_transform($activity, $id, $data_transformed);
            ActivityUtil::category_infos_transform($activity, $id, $data_transformed);
            ActivityUtil::function_infos_transform($activity, $id, $data_transformed);
            
            JMutil::valid_check("activity", $id, $data_transformed);
        }
        return $data_transformed;
    }

    public function write_xml($data)
    {
        global $CONFIG;
        $file_path = $CONFIG['file']['path']['activity'];
        if (! is_dir($file_path)) {
            mkdir($file_path, 0777, true);
        }
        $time = date("Y-m-d|H:i:s");
        $file = $file_path . "/" . "activity_full_import_" . $time . ".xml";

        // 需要建立data_import目录，然后给写权限
        require_once (PROJECT_ROOT . '/util/util_write.php');
        if (! ImportWrite::write_xml_add($data, $file)) {
            error_log("时间" . $time . "activity全量索引失败！！！", 3, "/home/logs/data_import/activity_import_error.log");
        } else {
            // 将本次索引时间记录下来
            $index_time = file_get_contents($CONFIG['file']['path']['data'] . "/activity.properties");
            $last_time = array();
            if (! empty($index_time)) {
                $tmp = explode("\n", $index_time);
                foreach ($tmp as $value) {
                    $tmp2 = explode("=", $value);
                    if ($tmp2[0] == "activity") {
                        $tmp2[1] = $time;
                    }
                    $last_time[$tmp2[0]] = $tmp2[0] . "=" . $tmp2[1];
                }
            }

            if (! isset($last_time['activity'])) {
                $last_time['activity'] = "activity=" . $time;
            }
            file_put_contents($CONFIG['file']['path']['data'] . "/activity.properties", implode("\n", $last_time));
        }
    }
}