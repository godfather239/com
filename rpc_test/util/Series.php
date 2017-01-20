<?php

/**
 *
 */
class SeriesTransform{

    public function getSeries($params) {
        require_once (PROJECT_ROOT . '/util/JMutil.php');
        $series = array();
        if(isset($params) && !empty($params)){
            $series = JMutil::getThriftClient('Series', 'getSeriesInfoByProductAndStore', array(
                $params,
                0
            ));
//            if(!empty($series_info)){
//                foreach ($series_info as $pid => $series_arr) {
//                    foreach ($series_arr as $sid => $info) {
//                        if (!empty($info)) {
//                            if(is_numeric($info['series_id'])){
//                                $series[$pid]['series_id'][] = $info['series_id'];
//                            }else{
//                                $series[$pid]['series_id'][] = 0;
//                                file_put_contents("/dev/shm/error.txt",date("Y-m-d|H:i:s")."入参：".var_export($params) ."\n".var_export($series_info,true)."\n",FILE_APPEND);
//                            }
//                            $series[$pid]['series_info'][] = $info['series_id'] . "," . $info['series_name'];
//                            $series[$pid]['series_name'][] = $info['series_name'];
//                        }
//                    }
//                }
//            }
        }
        return $series;
    }
}
