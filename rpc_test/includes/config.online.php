<?php
/**
 * This file is generated automatically by ConfigurationSystem.
 * Do not change it manually in production, unless you know what you're doing and can take responsibilities for the consequences of changes you make.
 */

global $CONFIG;

//每个job_id执行对应的脚本
$CONFIG['job_map'] = array(
    322 => "promotion_delta.php",
    
    //operateDeal
    248 => "global_deal_delta.php",
    
    //deal_inventory_sku
    254 => "sku_inventory_delta.php",
);
$CONFIG['class_map'] = array(
    322 => 'PromotionDelta',
    248 => 'GlobalDealDelta',
    254 => 'SkuInventoryDelta',
);

$CONFIG['thrift'] = array(
    // jumei_search_webservice address
    'Search' => array(
        'nodes' => array(
            0 => '172.20.5.65:9090',
        ),
        'provider' => PROJECT_ROOT . '/Provider',
        'timeout' => 30,
    ),
    'JumeiProduct_Read_Thrift_Product' => array(
        'nodes' => array (
  0 => '192.168.16.140:9092:1',
),
        'provider' => PROJECT_ROOT . '/Provider',
        'timeout' => 30,
        
        // 单位秒,（不设置默认30秒）
    ) ,
    'JumeiProduct_Search_Read_Deals' => array(
        'nodes' => array (
  0 => '192.168.16.140:9092:1',
),
        'provider' => PROJECT_ROOT . '/Provider',
        'timeout' => 30,
        
        // 单位秒,（不设置默认30秒）
    ) ,
    'PromotionFaceService' => array(
        'nodes' => array (
  0 => '172.20.5.77:9000',
),
        'provider' => PROJECT_ROOT . '/Provider',
        'timeout' => 30,
        
        // 单位秒,（不设置默认30秒）
    ) ,
    'SaleService' => array(
        'nodes' => array (
  0 => '172.20.16.22:9993:10',
),
        'provider' => PROJECT_ROOT . '/Provider',
        'timeout' => 30,
        // 单位秒,（不设置默认30秒）
    ) ,
    'Series' => array(
        'nodes' => array (
  0 => '172.20.4.120:9090:1',
) ,
        'provider' => PROJECT_ROOT . '/Provider',
        'timeout' => 30,
        
        // 单位秒,（不设置默认30秒）
    ) ,
    'Search_PopStore' => array(
        'nodes' => array (
  0 => '172.20.4.120:9090:1',
) ,
        'provider' => PROJECT_ROOT . '/Provider',
        'timeout' => 30,
    
        // 单位秒,（不设置默认30秒）
    ) ,
    'Search_Activities' => array(
        'nodes' => array (
  0 => '172.20.4.120:9090:1',
) ,
        'provider' => PROJECT_ROOT . '/Provider',
        'timeout' => 30,

        // 单位秒,（不设置默认30秒）
    ) ,
    'MerchantStore' => array(
        'nodes' => array (
  0 => '172.20.4.120:9090:1',
) ,
        'provider' => PROJECT_ROOT . '/Provider',
        'timeout' => 30,
        // 单位秒,（不设置默认30秒）
    ) ,
);

$CONFIG['RedisCache'] = array(
    'search' => array(
        'nodes' => array(
                0 => array (
                      'master' => '127.0.0.1:6379'
                )),
        'db' => 3
    ) ,
    'promocard' => array(
        'db' => 9,
        'nodes' => array (
  0 => 
  array (
    'master' => '192.168.53.17:27001',
  ),
)
    )
);

$CONFIG['RedisStorge'] = array(
    'search' => array(
        'nodes' => array(
                0 => array (
                    'master' => '127.0.0.1:6379'
                )),
        'db' => 3
    ) ,
    'promocard' => array(
        'db' => 9,
        'nodes' => array (
  0 => 
  array (
    'master' => '192.168.53.17:27001',
  ),
)
    )
);


//配置thrift 监控的名称
define('JM_APP_NAME', 'search-import');

//配置日志路径
$CONFIG['LogsPath'] = '/home/logs';

//监控日志路径
$CONFIG['MNLogger'] = array(
    'exception' => array(
        'on' => true,
        'app' => 'search-import',
        'logdir' => $CONFIG['LogsPath'] . '/monitor'
    ) ,
    'trace' => array(
        'on' => true,
        'app' => 'search-import',
        'logdir' => $CONFIG['LogsPath'] . '/monitor'
    ) ,
    'thrift' => array(
        'monitor_log_path' => $CONFIG['LogsPath'] . '/monitor',
        
        // 统一监控日志目录
        'trace_log_path' => $CONFIG['LogsPath'] . '/monitor',
        
        // 日志追踪目录
        'exception_log_path' => $CONFIG['LogsPath'] . '/monitor',
        
        // 异常日志目录
        'alarm_phone' => '',
        
        // 告警电话
        
        
    ) ,
);

//监控字段列表
$CONFIG['field'] = array(
    'global_deal' => array(
        'buyer_number',
        'start_time',
        'end_time',
        'status',
        'short_name',
        'category',
        'show_category',
        'real_buyer_number',
        'show_tag',
        'shipping_system_id',
        'tp_search_meta_text_custom',
    ) ,
);


$CONFIG['file']['path'] = array(
    'mall_product' => '/home/data/mall_product',
    'deal' => '/home/data/deal',
    'global_deal' => '/home/data/global_deal',
    'global_mall' => '/home/data/global_mall',
    'pop' => '/home/data/pop',
    'pop_mall' => '/home/data/pop_mall',
    'global_pop_mall' => '/home/data/global_pop_mall',
    'store' => '/home/data/store',
    'activity' => '/home/data/activity',
    'data' => '/home/data',
    );

$CONFIG['server'] = array(
    'solr_master' => '172.20.5.65:8080',
    'redis_ip' => '127.0.0.1',
    'redis_port' => '6379',
);

$CONFIG["shipping_system_metatext"] = array(
    "2754" => "香港直邮",
    "2967" => "澳门直邮"
);

//促销生效环境
$CONFIG["promotion_effect_env"] = 'dev';

// 类型与处理类映射
$CONFIG["importer_mapping"] = array(
    "global_mall"   =>  "GlobalMallFullImport",
    "global_deal"   =>  "GlobalDealFullImport",
    "mall_product"  =>  "MallFullImport",
    "deal"          =>  "DealFullImport",
    "pop"           =>  "PopFullImport",
    "pop_mall"      =>  "PopMallFullImport",
    "global_pop_mall"   =>  "GlobalPopMallFullImport"
);

// 促销类型和描述开关
$CONFIG["promotion_rules"] = array (
  'add_description' => true,
  'info' => 
  array (
    'gift' => '满赠',
    'over_reduce' => '满减',
    'over_reduce_uncapped' => '不封顶满减',
    'over_saleoff' => '金额满就折',
    'over_qty_saleoff' => '件数满就折',
    'discount_2nd_piece' => '第二件打折',
    'red_envelope' => '虚拟商品',
    'gift_random' => '随机赠品',
    'voucher' => '满返',
    'voucher_uncapped' => '不封顶满返',
    'over_specialoffer' => '加价购',
  ),
);

//jumei_price字段赋值是否优先使用value_of_goods字段开关
$CONFIG["jumei_price_assigned"] = array (
  'use_value_of_goods' => false,
);

$CONFIG['postproc'] = '#{Search-Admin.jumei.postproc}';

$CONFIG['shell_conf'] = array (
  'shell_scripts_dir' => '/home/shell',
  'master_host' => '172.20.5.65:8080',
  'slave_host' => 
  array (
  ),
);

$CONFIG['source_valid_check'] = array (
  'check' => true,
  'use_default' => true,
);
$CONFIG['log']['common'] = "/home/logs/data_import/index_common.log";
$CONFIG['log']['src_ids'] = "/home/logs/data_import/src.ids.log";
$CONFIG['delta_index']['params'] = "#{Search-Admin.jumei.delta_index.params}";
$CONFIG['activity_attr'] = array (
  'category_v3_1' => 
  array (
    'prod_min' => 10,
    'ratio_min' => 0.5,
    'limit' => 1,
    'weight' => 2,
  ),
  'category_v3_2' => 
  array (
    'prod_min' => 10,
    'ratio_min' => 0.3,
    'limit' => 3,
    'weight' => 3,
  ),
  'category_v3_3' => 
  array (
    'prod_min' => 10,
    'ratio_min' => 0.1,
    'limit' => 10,
    'weight' => 4,
  ),
  'category_v3_4' => 
  array (
    'prod_min' => 30,
    'ratio_min' => 0.05,
    'limit' => 10,
    'weight' => 5,
  ),
  'brand' => 
  array (
    'prod_min' => 10,
    'ratio_min' => 0.3,
    'limit' => 3,
    'weight' => 8,
  ),
  'function' => 
  array (
    'prod_min' => 10,
    'ratio_min' => 0.2,
    'limit' => 5,
    'weight' => 1,
  ),
);