<?php
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
    'JumeiProduct_Read_Thrift_Product' => array(
        'nodes' => "#{product-service.backend.thrift.iplist}",
        'provider' => PROJECT_ROOT . '/Provider',
        'timeout' => 30,
        
        // 单位秒,（不设置默认30秒）
    ) ,
    'JumeiProduct_Search_Read_Deals' => array(
        'nodes' => "#{product-service.backend.thrift.iplist}",
        'provider' => PROJECT_ROOT . '/Provider',
        'timeout' => 30,
        
        // 单位秒,（不设置默认30秒）
    ) ,
    'PromotionFaceService' => array(
        'nodes' => "#{Promotion.interface.servers}",
        'provider' => PROJECT_ROOT . '/Provider',
        'timeout' => 30,
        
        // 单位秒,（不设置默认30秒）
    ) ,
    'SaleService' => array(
        'nodes' => "#{cube.service.search.product_sales}",
        'provider' => PROJECT_ROOT . '/Provider',
        'timeout' => 30,
        // 单位秒,（不设置默认30秒）
    ) ,
    'Series' => array(
        'nodes' => "#{Flagship_Service.backstage.Thrift.Nodes}" ,
        'provider' => PROJECT_ROOT . '/Provider',
        'timeout' => 30,
        
        // 单位秒,（不设置默认30秒）
    ) ,
    'Search_PopStore' => array(
        'nodes' => "#{Flagship_Service.backstage.Thrift.Nodes}" ,
        'provider' => PROJECT_ROOT . '/Provider',
        'timeout' => 30,
    
        // 单位秒,（不设置默认30秒）
    ) ,
    'Search_Activities' => array(
        'nodes' => "#{Flagship_Service.backstage.Thrift.Nodes}" ,
        'provider' => PROJECT_ROOT . '/Provider',
        'timeout' => 30,

        // 单位秒,（不设置默认30秒）
    ) ,
    'MerchantStore' => array(
        'nodes' => "#{Flagship_Service.backstage.Thrift.Nodes}" ,
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
        'db' => "#{PromoCard.Redis.promoCard.db}",
        'nodes' => "#{Res.Redis.Usercenter.Storage}"
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
        'db' => "#{PromoCard.Redis.promoCard.db}",
        'nodes' => "#{Res.Redis.Usercenter.Storage}"
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
    'solr_master' => "#{Search-Admin.solr.host}",
    'redis_ip' => '127.0.0.1',
    'redis_port' => '6379',
);

$CONFIG["shipping_system_metatext"] = array(
    "2754" => "香港直邮",
    "2967" => "澳门直邮"
);

//促销生效环境
$CONFIG["promotion_effect_env"] = "#{Search-Admin.promotion.stage}";

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
$CONFIG["promotion_rules"] = "#{Search-Admin.promotion.rules}";

//jumei_price字段赋值是否优先使用value_of_goods字段开关
$CONFIG["jumei_price_assigned"] = "#{Search-Admin.jumei.price.assigned}";

$CONFIG['postproc'] = '#{Search-Admin.jumei.postproc}';

$CONFIG['shell_conf'] = "#{Search-Admin.Shell.Conf}";

$CONFIG['source_valid_check'] = "#{Search-Admin.jumei.source.valid_check}";
$CONFIG['log']['common'] = "/home/logs/data_import/index_common.log";
$CONFIG['log']['src_ids'] = "/home/logs/data_import/src.ids.log";
$CONFIG['delta_index']['params'] = "#{Search-Admin.jumei.delta_index.params}";
$CONFIG['activity_attr'] = "#{Search-Admin.Activity.Attr}";
