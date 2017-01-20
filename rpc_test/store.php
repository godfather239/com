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


require_once(PROJECT_ROOT . "/Import/StoreFullImport.php");

echo "full_import start...\n";
echo "start time " . date("Y-m-d|H:i:s")."\n";

try{
    $importer = new StoreFullImport();
    $importer->write_xml($importer->field_transform($importer->read_original_data()));
}catch(Exception $ex){
    var_dump($ex);
    exit(2);
}

echo "end time " . date("Y-m-d|H:i:s")."\n";
echo "full_import end...";
