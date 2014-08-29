<?php
/**
 * User: NaeemM
 * Date: 19/03/14
 */

require_once('indexing_settings.php');

exec("nohup sh reindex_sort.sh > ".$media_directory."/reindex_ca_crkc_sort.txt 2> ".$media_directory."/reindex_ca_crkc_sort.txt.err < /dev/null &");


