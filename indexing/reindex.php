<?php
/**
 * User: NaeemM
 * Date: 19/03/14
 */
require_once('indexing_settings.php');

exec("nohup sh reindex.sh > ".$media_directory."/reindex_ca_crkc.txt 2> ".$media_directory."/reindex_ca_crkc.txt.err < /dev/null &");

