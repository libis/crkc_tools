<?php
//exec("php support/utils/createSolrConfiguration.php > /www/libis/vol03/collectiveaccess/crkc_media/config_log.log 2> /www/libis/vol03/collectiveaccess/crkc_media/config_log_error.txt");
exec("php support/utils/reindex.php 'www.religieuserfgoed.be' > /www/libis/vol03/collectiveaccess/crkc_media/reindex_solr_ca.log 2> /www/libis/vol03/collectiveaccess/crkc_media/reindex_solr_error.txt");

?>
