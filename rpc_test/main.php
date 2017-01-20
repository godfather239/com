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
echo "Hello world\n";

//$flag = 0;
//$num = 10;
//$ret = JMutil::retryClient('JumeiProduct_Search_Read_Deals', 'getJumeiDataWithMinDealIdAndLimit', array($flag, $num));

/**
 * pseudocode
 * open test case files
 * for test_case in files
 *      extract search_params from test_case
 *      answer = send thrift request 
 *      for check_pattern in test_case
 *          if !match(check_pattern, answer)
 *              echo    test_case.name,failed
 *              break
 *      echo test_case.name,succeed
 */

$test_cases = array();
$case = array();
$case['params'] = array('search' => '面膜','rows_per_page' => '1');
$case['check_pattern'] = array("");
$param = array(json_encode(array('search' => '面膜','rows_per_page' => '1')));
$ret = \JMutil::retryClient('Search', 'getSearchData_v4', $param);
echo $ret."\n";
