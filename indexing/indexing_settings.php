c<?php
/**
 * User: NaeemM
 * Date: 19/03/14
 */
$home_directory = '/www/libis/web/lias_html/crkc_sandbox_final';
$_SERVER['SCRIPT_FILENAME'] = $home_directory .'/index.php';
putenv('COLLECTIVEACCESS_HOME='. $home_directory);
require_once($home_directory.'/setup.php');


ini_set('memory_limit', '1024M');
set_time_limit(0);
$media_directory = '/www/libis/web/lias_html/collectiveaccess/crkc_media';