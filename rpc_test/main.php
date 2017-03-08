<?php
/**
 * Created by IntelliJ IDEA.
 * User: greenday
 * Date: 17-1-20
 * Time: 下午4:25
 */
define('PROJECT_ROOT', realpath(dirname(__FILE__)));
date_default_timezone_set('Asia/Shanghai');

if (file_exists(PROJECT_ROOT . "/includes/config.php")) {
    include (PROJECT_ROOT . "/includes/config.php");
} else {
    include (PROJECT_ROOT . "/includes/config.online.php");
}

//  公用类库自动初始化
require_once(PROJECT_ROOT . '/Vendor/Bootstrap/Autoloader.php');
Bootstrap\Autoloader::instance()->init();
global $CONFIG;
\MNLogger\EXLogger::setUp(array('exception'=>$CONFIG['MNLogger']['exception']));
\MNLogger\TraceLogger::setUp(array('trace'=>$CONFIG['MNLogger']['trace']));
\MNLogger\TraceLogger::instance('trace')->HTTP_SR();

require_once (PROJECT_ROOT . '/util/JMutil.php');
require_once (PROJECT_ROOT . '/util/EasyLexer.php');
require_once (PROJECT_ROOT . '/util/common.php');
require_once (PROJECT_ROOT . '/PHPExcel/PHPExcel.php');
require_once (PROJECT_ROOT . '/PHPExcel/PHPExcel/IOFactory.php');

function process($filepath) {
    static $column_conf = array('name' => 'A', 'param' => 'B', 'assert' => 'C');
    
    $obj_excel = PHPExcel_IOFactory::load($filepath);
    $sheet_names = $obj_excel->getSheetNames();
    foreach ($sheet_names as $sheet_name) {
        echo ".....................Start testing {$sheet_name}.................\n";
        $work_sheet = $obj_excel->getSheetByName($sheet_name);
        // The first row is head, ignore it
        $res = array();
        for ($row = 2; $row <= $work_sheet->getHighestDataRow(); $row++) {
            $case = array();
            foreach ($column_conf as $key => $value) {
                $cell_val = $work_sheet->getCell($value.$row)->getValue();
                $case[$key] = $cell_val;
            }
            $res[] = $case;
        }
        testCases($res, $sheet_name);
        echo ".....................Testing {$sheet_name} finished!.................\n";
    }
}

function testCases($cases, $method) {
    static $providers = array('getSearchData_v4' => 'Search', 'getSearchStore_v3' => 'Search_Store');
    
    global $CONFIG;
    foreach ($cases as $case) {
        $data = doRPCRequest($providers[$method], $method, $case['param']);
        $param = json_decode($case['param'], true);
        if (isset($data['correct_keyword'])) {
            $param['search'] = $data['correct_keyword'];
        } else if (isset($data['corrected_keyword'])) {
            $param['search'] = $data['corrected_keyword'];
        }
        $str = EasyLexer::parse($case['assert'], $param, 'data');
//        echo $str."\n";
        if (isset($CONFIG['debug']) && $CONFIG['debug']) {
            echo $str."\n";
        }
        try {
            $ret = eval("return ".$str.";");
        } catch (Exception $e) {
            echo $e."\n";
            $ret = false;
        }
        $ret = $ret?'Succeed':'Failed';
        printf("%'.-60s%20s\n", $case['name'], $ret);
//        echo "{$case['name']}\t{$ret}\n";
    }
}

function doRPCRequest($provider, $method, $param_str) {
    return json_decode(JMutil::retryClient($provider, $method, array($param_str)), true);
}


echo ".....................test started.................\n";
process("/home/greenday/dev/bitbucket/maoyan_test.xlsx");
echo ".....................test finished.......................\n";
