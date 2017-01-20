#!/usr/bin/env php
<?php
/**
 * Created by IntelliJ IDEA.
 * User: greenday
 * Date: 16-10-27
 * Time: 下午7:13
 */
//require_once("/home/www/jumei_search_index/includes/config.php");
require_once(dirname(__FILE__) . "/../includes/config.php");

global $CONFIG;
if (isset($CONFIG['shell_conf'])) {
    $file_path = $CONFIG['shell_conf']['shell_scripts_dir'] . "/conf.src.new";
    file_put_contents($file_path, "");
    file_put_contents($file_path, "master_host={$CONFIG['shell_conf']['master_host']}\n", FILE_APPEND);
    file_put_contents($file_path, "slave_host=(\n", FILE_APPEND);
    foreach ($CONFIG['shell_conf']['slave_host'] as $key => $host) {
        file_put_contents($file_path, "{$host}\n", FILE_APPEND);
    }
    file_put_contents($file_path, ")\n", FILE_APPEND);
    // write other configs
    
    // rename file
    rename($file_path, $CONFIG['shell_conf']['shell_scripts_dir']."/conf.src");
}