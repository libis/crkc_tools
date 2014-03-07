<?php
//exec("php support/utils/createSolrConfiguration.php > /www/libis/vol03/collectiveaccess/crkc_media/config_log.log 2> /www/libis/vol03/collectiveaccess/crkc_media/config_log_error.txt");
exec("nohup sh reindex.sh > /www/libis/vol03/collectiveaccess/crkc_media/process.out 2> /www/libis/vol03/collectiveaccess/crkc_media/process.err < /dev/null &");

?>
