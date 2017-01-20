<?php
define('PROJECT_ROOT', realpath(dirname(__FILE__)));
date_default_timezone_set('Asia/Shanghai');

if (file_exists(PROJECT_ROOT . "/includes/config.php"))
{
    include (PROJECT_ROOT . "/includes/config.php");
} else
{
    include (PROJECT_ROOT . "/includes/config.online.php");
}

//  公用类库自动初始化
require_once(PROJECT_ROOT . '/Vendor/Bootstrap/Autoloader.php');
Bootstrap\Autoloader::instance()->init();
global $CONFIG;
\MNLogger\EXLogger::setUp(array('exception'=>$CONFIG['MNLogger']['exception']));
\MNLogger\TraceLogger::setUp(array('trace'=>$CONFIG['MNLogger']['trace']));
\MNLogger\TraceLogger::instance('trace')->HTTP_SR();



/**
 *
 * 全量开始，文件全部生成成功才是开始，否则失败。可以启用多线程来实现文件的快速生成
 *
 * */

require_once(PROJECT_ROOT . "/Import/MallFullImport.php");
require_once(PROJECT_ROOT . "/Import/DealFullImport.php");

require_once(PROJECT_ROOT . "/Import/GlobalDealFullImport.php");

require_once(PROJECT_ROOT . "/Import/PopFullImport.php");
require_once(PROJECT_ROOT . "/Import/GlobalMallFullImport.php");
require_once(PROJECT_ROOT . "/Import/PopMallFullImport.php");
require_once(PROJECT_ROOT . "/Import/GlobalPopMallFullImport.php");

try{
    echo "full_import start...\r\n";
    echo "mall product start...\r\n";
    echo "mall start time " . date("Y-m-d|H:i:s") . "\r\n";

    $mall_full_import = new MallFullImport();
    $mall_full_import->write_xml($mall_full_import->read_original_data());

    echo "mall product end ...\r\n";
    echo "mall end time " . date("Y-m-d|H:i:s") . "\r\n";
    echo "deal start ...\r\n";
    echo "deal start time " . date("Y-m-d|H:i:s") . "\r\n";

    $deal_full_import = new DealFullImport();
    $deal_full_import->write_xml($deal_full_import->read_original_data());

    echo "deal end ...\r\n";
    echo "deal end time " . date("Y-m-d|H:i:s") . "\r\n";
    echo "global deal start...\r\n";
    echo "global start time " . date("Y-m-d|H:i:s") . "\r\n";


    $global_deal_full_import = new GlobalDealFullImport();
    $global_deal_full_import->write_xml($global_deal_full_import->read_original_data());

    echo "global deal end ...\r\n";
    echo "global deal end time " . date("Y-m-d|H:i:s") . "\r\n";
    echo "pop start ...\r\n";
    echo "pop start time " . date("Y-m-d|H:i:s") . "\r\n";

    $pop_full_import = new PopFullImport();
    $pop_full_import->write_xml($pop_full_import->read_original_data());

    echo "pop end ...\r\n";
    echo "pop end time " .date("Y-m-d|H:i:s"). "\r\n";

    echo "global_mall start ...\r\n";
    echo "global_mall start time" . date("Y-m-d|H:i:s") . "\r\n";


    $global_mall_full_import = new GlobalMallFullImport();
    $global_mall_full_import->write_xml($global_mall_full_import->read_original_data());

    echo "global_mall end ...\r\n";
    echo "global_mall end time " .date("Y-m-d|H:i:s") . "\r\n";


    echo "pop_mall start ...\r\n";
    echo "pop_mall start time" . date("Y-m-d|H:i:s") . "\r\n";

    $pop_mall_full_import = new PopMallFullImport();
    $pop_mall_full_import->write_xml($pop_mall_full_import->read_original_data());

    echo "pop_mall end ...\r\n";
    echo "pop_mall end time " .date("Y-m-d|H:i:s") . "\r\n";

    /*    echo "global_pop_mall start ...\r\n";
        echo "global_pop_mall start time" . date("Y-m-d|H:i:s") . "\r\n";

        $global_pop_mall_full_import = new GlobalPopMallFullImport();
        $global_pop_mall_full_import->write_xml($global_pop_mall_full_import->field_transform($global_pop_mall_full_import->read_original_data()));

        echo "global_pop_mall end ...\r\n";
        echo "global_pop_mall end time" . date("Y-m-d|H:i:s") . "\r\n";*/
}catch(Exception $ex){
    var_dump($ex);
    exit(2);
}