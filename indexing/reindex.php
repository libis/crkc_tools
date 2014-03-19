<?php
/**
 * User: NaeemM
 * Date: 19/03/14
 */
require_once('/www/libis/web/lias_html/crkcsandbox_test/setup.php');
require_once('indexing_setting.php');

exec("nohup sh reindex.sh > /www/libis/web/lias_html/collectiveaccess/crkc_media/reindex_crkcsandbox_release.txt 2> /www/libis/web/lias_html/collectiveaccess/crkc_media/reindex_crkcsandbox_release.txt.err < /dev/null &");

