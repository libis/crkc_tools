#!/bin/bash
cd support/utils
nohup php reindex.php 'www.religieuserfgoed.be'  > /www/libis/vol03/collectiveaccess/crkc_media/reindex_solr_ca.log 2> /www/libis/vol03/collectiveaccess/crkc_media/reindex_solr_error.txt < /dev/null &
cd -


