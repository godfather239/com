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
require_once (PROJECT_ROOT . '/PHPExcel/PHPExcel.php');
require_once (PROJECT_ROOT . '/PHPExcel/PHPExcel/IOFactory.php');

function readCasesFromFile($filepath) {
    static $column_conf = array('name' => 'A', 'param' => 'B', 'assert' => 'C');
    
    $obj_excel = PHPExcel_IOFactory::load($filepath);
    $res = array();
    $work_sheet = $obj_excel->getActiveSheet();
    // The first row is head, ignore it
    for ($row = 2; $row <= $work_sheet->getHighestRow(); $row++) {
        $case = array();
        foreach ($column_conf as $key => $value) {
            $cell_val = $work_sheet->getCell($value.$row)->getValue();
            $case[$key] = $cell_val;
        }
        $res[] = $case;
    }
    return $res;
}

function doRPCRequest($provider, $method, $param_str) {
    return json_decode(JMutil::retryClient($provider, $method, array($param_str)), true);
}


echo ".....................test started.................\n";
$cases = readCasesFromFile("/home/greenday/Documents/test.csv");
foreach ($cases as $case) {
    $data = doRPCRequest('Search', 'getSearchData_v4', $case['param']);
    $param = json_decode($case['param'], true);
    $str = EasyLexer::parse($case['assert'], $param, 'data');
    $ret = eval("return ".$str.";");
    $ret = $ret?'Succeed':'Failed';
    echo "{$case['name']}\t{$ret}\n";
}
echo ".....................test finished.......................\n";
echo ".....................test finished.......................\n";
