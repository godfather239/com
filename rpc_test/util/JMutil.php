<?php
class JMutil{
	static private $prod_numeric_fields = array(
		"discount",
		"market_price",
		"jumei_price",
		"status",
		"area_code",
		"area_exchange_rate",
		"area_currency_symbol_location",
		"abroad_price",
		"is_sellable",
		"shipping_system_id",
		"package_price",
		"package_id",
		"deal_real_buyer_number",
		"deal_id",
		"countries",
		"start_time",
		"end_time",
		"buyer_number",
		"fake_30day_buyer_number",
		"mall_real_buyer_number",
		"real_30day_mall_sale_volume",
		"real_30day_deal_sale_volume",
		"real_30day_buyer_number",
		"fake_30day_buyer_number",
		"real_30day_sales_amount",
		"sort_sales_volume",
		"sort_sales_amount",
		"sort_price",
		"sort_start_date",
		"sort_popularity",
		"sale_price",
		"sale_start_time",
		"sale_end_time",
		"show_status",
		"mall_id",
		"is_authorization",
		"product_reports_number",
		"product_report_rating",
		"deal_comments_number",
		"is_available_bj",
		"is_available_cd",
		"is_available_gz",
		"is_available_sh",
		"merchant_id",
		"wish_number",
		"is_published_price",
		"high_priority_sort",
		"deal_sort",
		"sale_amount",
		"saved_amount",
		"payment_start_time",
		"payment_end_time",
		"is_new_tag_time",
	);
	static private $store_numeric_fields = array(
		"fav_count",
		"product_count",
		"is_authorization",
		"is_proprietary",
		"is_pc_store",
		"is_mobile_store",
		"sales_volume_30",
		"sales_num_30",
	);
	static private $activity_numeric_fields = array(
		"brand_id",
		"end_time",
		"preheatting_time",
		"sales_volume_mobile_pay",
		"start_time",
	);
	static private $dyn_numeric_fields = array(
		"_float_dynamic",
		"_long_dynamic",
		"_int_dynamic",
	);
	static private $core_fields_map = array(
		"product" => array(
			"product_id",
			"doc_id",
			"doc_type"
		),
		"store" => array(
			"id"
		),
		"activity" => array(
			"id"
		)
	);
	public static function getThriftClient($provider,$method = null,$params = array())
	{
		try {
			global $CONFIG;
			\Thrift\Client::config($CONFIG['thrift']);
			\Thrift\Client::extConfig($CONFIG['MNLogger']['thrift']);
			$client = \Thrift\Client::instance($provider);
			if (empty($method))
			{
				return $client;
			}
			return call_user_func_array(array($client, $method), $params);
		}catch(Exception $e){
		    file_put_contents("/home/logs/thriftErr.log", date("Y-m-d|H:i:s")."错误method:".$method."\n", FILE_APPEND);
		    file_put_contents("/home/logs/thriftErr.log", date("Y-m-d|H:i:s").'Message:'.$e->getMessage()."\n", FILE_APPEND);
		    return null; 
		}
	}
	

	public static function retryClient($provider,$method,$params = array(),$retry=1){
	    try{
	        global $CONFIG;
	        \Thrift\Client::config($CONFIG['thrift']);
	        \Thrift\Client::extConfig($CONFIG['MNLogger']['thrift']);
	        $client = \Thrift\Client::instance($provider);
	        return call_user_func_array(array($client, $method), $params);
	    }catch(Exception $e){
	        $retry++;
	        if($retry<4){
	            return self::retryClient($provider,$method,$params,$retry);
	        }else{
	            file_put_contents("/home/logs/thrift/".date("ymd").".log", date("Y-m-d|H:i:s")."异常method:".$method."\n", FILE_APPEND);
	            //file_put_contents("/home/logs/thriftErr.log", date("Y-m-d|H:i:s")."args:".var_export($params,1)."\n", FILE_APPEND);
	            file_put_contents("/home/logs/thrift/".date("ymd").".log", date("Y-m-d|H:i:s").'Message:'.$e->getMessage()."\n", FILE_APPEND);
	            throw new Exception("重试三次后失败：".$e->getMessage());
	        }
	    }
	}
	
	public static function getThriftClient2($provider,$method = null,$params = array())
	{
	        global $CONFIG;
	        \Thrift\Client::config($CONFIG['thrift']);
	        \Thrift\Client::extConfig($CONFIG['MNLogger']['thrift']);
	        $client = \Thrift\Client::instance($provider);
	        if (empty($method))
	        {
	            return $client;
	        }
	        return call_user_func_array(array($client, $method), $params);
	    
	}


	public static function two_dimensional_array_merge($source, $destination){
		foreach ($destination as $key => $value) {
			if(isset($source[$key]) && !empty($source[$key])){
				$destination[$key] = $destination[$key] + $source[$key];
			}
		}
		return $destination;
	}

	/**
	 * 合并hashId
	 * @param $source
	 * @param $destination
	 * @return array
	 */
	public static function array_merge_for_hashId($source, $destination){
		foreach ($destination as $hashId => $value) {
			//红包属于deal,并且红包的product_id为空，所以deal要对product_id是否为空做校验
			if(isset($value["product_id"]) && !empty($value["product_id"])){
				$pid = $value["product_id"];
				if(isset($source[$pid]) && !empty($source[$pid])){
					$destination[$hashId] = $destination[$hashId] + $source[$pid];
				}
			}
		}
		return $destination;
	}

	/**
	 * 合并mallId
	 * @param $source
	 * @param $destination
	 * @return array
	 */
	public static function array_merge_for_mallId($source, $destination){
		foreach ($destination as $mallid => $value) {
			$pid = $value["product_id"];
			if(isset($source[$pid]) && !empty($source[$pid])){
//				$destination[$mallid] = $destination[$mallid] + $source[$pid];
				$destination[$mallid] = $destination[$mallid] + $source[$pid];
			}
		}
		return $destination;
	}

	public static function array_merge_for_series($source, $destination) {
		foreach ($destination as $id => $value) {
			if (!isset($value['product_id'])) {
				continue;
			}
			$pid = $value['product_id'];
			if(isset($source[$pid]) && !empty($source[$pid])) {
				$destination[$id]['series_info_orig'] = $source[$pid];
			}
		}
		return $destination;
	}
	
	/**
	 * global mall的商品
	 * 从促销获取到的信息是以pid为键
	 * 从商品库获取到的信息是以mall_id为键
	 * 不能使用two_dimensional_array_merge来merge
	 * @param $products
	 * @param $promotions
	 * @return array
	 */
	public static function array_merge_for_globalmall($promotions, $products){
		foreach ($products as $mallid => $value) {
			$pid = $value["product_id"];
			if(isset($promotions[$pid]) && !empty($promotions[$pid])){
				$products[$mallid] = $products[$mallid] + $promotions[$pid];
			}
		}
		return $products;
	}


	public static function mergeByIndenity($source, $destination, $identityField) {
		if (!empty($source) && !empty($destination)) {
			foreach ($source as $k => $v) {
				foreach ($destination as $ak => $av) {
					if(!empty($source[$k][$identityField])) {
						if ($source[$k][$identityField] == $ak) {
							$source[$k] = array_merge($source[$k], $destination[$ak]);
                            continue;
                        }
					}
				}
			}
		}
		return $source;
	}

	public static function collectionKeys($json_array, $field){
		$ret = array();
		if (!empty($json_array) && !empty($field)) {
			foreach($json_array as $json){
				if(isset($json[$field])){
					$ret[] = $json[$field];
				}
			}
		}
		return $ret;
	}

	/**
	 * 通过productid和store_id构造数组
	 * @param $product
	 * @return array
	 */
	public static function array_productId_storeId($product){
		$params = array();
		foreach ($product as $value) {
			if(isset($value['store_id']) && !empty($value['store_id'])){
				if(array_key_exists($value['product_id'],$params)){
					$params[$value['product_id']]=JMutil::mergeArray($params[$value['product_id']],$value['store_id']);
				}else{
					$params[$value['product_id']] = $value['store_id'];
				}
			}
		}
		return $params;
	}

	/**
	 *  拆分、扩展字符串的子元素
	 *  例如,输入$array["123,456","7","7"] $delimiter:","
	 *  则会被拆分为["123","456","7","7"]
	 * @param $array
	 * @param $delimiter
	 * @return array
	 */
	public static function expandArrayByDelimiter($array, $delimiter){
		$result = array();
		try{
			foreach($array as $rawId){
				if($rawId && $rawId != "[]" && strpos($rawId, ",")) {
					$temp_ids = explode($delimiter, $rawId);
					$result = array_merge($result, $temp_ids);
				}else{
					$result[] = $rawId;
				}
			}
		}catch(Exception $e){
			var_dump($e->getMessage());
			return $array; // mute, not interrupt process
		}
		return $result;
	}


	public static function  mergeArray($array1,$array2){
           foreach($array2 as $value){
			   if(!in_array($value,$array1)){
				   array_push($array1,$value);
			   }
		}
		return $array1;
	}

	public static function valid_check($type, $id, &$data_transformed) {
		global $CONFIG;
		if (!isset($CONFIG['source_valid_check']['check']) || !$CONFIG['source_valid_check']['check']) {
			return true;
		}
		
		
		if ($type == "product") {
			$fields = self::$prod_numeric_fields;
		} else if ($type == 'store') {
			$fields = self::$store_numeric_fields;
		} else {
			$fields = self::$activity_numeric_fields;
		}
		$use_default = isset($CONFIG['source_valid_check']['use_default']) ? $CONFIG['source_valid_check']['use_default'] : false;
		$core_fields = self::$core_fields_map[$type];
		foreach ($data_transformed[$id] as $field => $value) {
			if (in_array($field, $core_fields) && (!isset($data_transformed[$id][$field]))) {
				self::logError("[dirty data]-{$type}-{$field}, ".json_encode($data_transformed[$id]));
				unset($data_transformed[$id]);
				return false;
			}
			
			$should_check = false;
			if (in_array($field, $fields)) {
				$should_check = true;
			}
			foreach (self::$dyn_numeric_fields as $dynf) {
				if (strpos($field, $dynf) !== false) {
					$should_check = true;
					break;
				}
			}
			if (!$should_check) {
				continue;
			}
			if (!isset($value) || !is_numeric($value)) {
				self::logError("[dirty data]-{$type}-{$field}, ".json_encode($data_transformed[$id]));
				if ($use_default) {
					$data_transformed[$id][$field] = 0;
				} else {
					unset($data_transformed[$id]);
					return false;
				}
			}
		}
		return true;
	}

	static public function logError($msg_str) {
		global $CONFIG;
		file_put_contents($CONFIG['log']['common'], "[ERROR] - ".date("Y-m-d H:i:s")." - ".$msg_str."\n", FILE_APPEND);
	}

	static public function logInfo($msg_str) {
		global $CONFIG;
		file_put_contents($CONFIG['log']['common'], "[INFO] - ".date("Y-m-d H:i:s")." - ".$msg_str."\n", FILE_APPEND);
	}
}