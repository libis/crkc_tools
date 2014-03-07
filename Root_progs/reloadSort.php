<?php
//exec("php support/utils/createSolrConfiguration.php > /www/libis/web/collectiveaccess/crkc_media/config_log.log 2> /www/libis/vol03/collectiveaccess/crkc_media/config_log_error.txt");
exec("nohup sh reloadSort.sh > /www/libis/web/collectiveaccess/crkc_media/process_reload.out 2> /www/libis/vol03/collectiveaccess/crkc_media/process_reload.err < /dev/null &");

?>
