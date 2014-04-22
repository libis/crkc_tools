<?php
/**

 *
 * Date: 19/03/14
 */

require_once('indexing_setting.php');

exec("nohup sh reindex_sort.sh > /www/libis/web/lias_html/collectiveaccess/crkc_media/reindex_crkcsandbox_release_sort.txt 2> /www/libis/web/lias_html/collectiveaccess/crkc_media/reindex_crkcsandbox_release_sort.txt.err < /dev/null &");


