#!/bin/bash
cd /www/libis/web/lias_html/crkc_sandbox_final/support/bin
nohup php caUtils rebuild-search-index  > /www/libis/web/lias_html/collectiveaccess/crkc_media/reindex_ca_crkc.log 2> /www/libis/web/lias_html/collectiveaccess/crkc_media/reindex_ca_crkc_error.txt < /dev/null &
cd -