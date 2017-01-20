<?php
global $CONFIG;

//每个job_id执行对应的脚本
$CONFIG['job_map'] = array(
    322 => "promotion_delta.php",
    327 => "global_deal_delta.php",
    325 => "sku_inventory_delta.php",
);
$CONFIG['class_map'] = array(
    322 => 'PromotionDelta',
    327 => 'GlobalDealDelta',
    325 => 'SkuInventoryDelta',
);

$CONFIG['thrift'] = array(
    'JumeiProduct_Read_Thrift_Product' => array(
        'nodes' => array(
            '192.168.16.140:9092',
        ) ,
        'provider' => PROJECT_ROOT . '/Provider',
        'timeout' => 100,
        
        // 单位秒,（不设置默认30秒）
        
    ) ,
);

$CONFIG['db'] = array(
    'tuanmei' => array(
        'dsn' => 'mysql:host=192.168.20.71;port=9001;dbname=tuanmei',
        'user' => 'dev',
        'password' => 'jmdevcd',
        'confirm_link' => true,
         //required to set to TRUE in daemons.
        'options' => array(
            \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'latin1\'',
            \PDO::ATTR_TIMEOUT => 3
        )
    ) ,
);


$CONFIG['RedisCache'] = array(
    'search' => array(
        'nodes' => array(
                0 => array (
                    'master' => '127.0.0.1:6379'
                )),
        'db' => 4
    ) ,
);

//配置thrift 监控的名称
define('JM_APP_NAME', 'search-import');

//配置日志路径
$CONFIG['LogsPath'] = '/Users/qinglouer/www/log';

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
    'mall_product' => '/Users/qinglouer/www/test/mall_product/',
    'deal' => '/Users/qinglouer/www/test/deal/',
    'global_deal' => '/Users/qinglouer/www/test/global_deal/',
    'global_mall' => '/Users/qinglouer/www/test/global_mall/',
    'pop' => '/Users/qinglouer/www/test/pop/',
);

$CONFIG["shipping_system_metatext"] = array(
    "2754" => "香港直邮",
    "2967" => "澳门直邮"
);

//促销生效环境
$CONFIG["promotion_effect_env"] = "publish";

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
