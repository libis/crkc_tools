<?php
/**
 * User: NaeemM
 * Date: 19/03/14
 */
require_once('/www/libis/web/lias_html/crkcsandbox_test/setup.php');

$_SERVER['SCRIPT_FILENAME'] = '/www/libis/web/lias_html/crkcsandbox_test/index.php';
putenv('COLLECTIVEACCESS_HOME=/www/libis/web/lias_html/crkcsandbox_test/');
ini_se('memory_limit', '1024M');
set_time_limit(0);