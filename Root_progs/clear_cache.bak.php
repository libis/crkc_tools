<?php
// Dit script kan gebruikt worden om bestanden die niet verwijderd kunnen worden te verwijderen.
exec("rm -f app/tmp/ca* > /www/libis/vol03/collectiveaccess/crkc_media/testclearcache.txt 2> /www/libis/vol03/collectiveaccess/crkc_media/testclearcache_error.txt");
?>
