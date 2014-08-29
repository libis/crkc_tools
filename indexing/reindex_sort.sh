#!/bin/bash
cd /www/libis/web/lias_html/crkc_sandbox_final/support/bin
nohup php caUtils rebuild-sort-values  > /www/libis/web/lias_html/collectiveaccess/crkc_media/reindex_ca_crkc_sort.log 2> /www/libis/web/lias_html/collectiveaccess/crkc_media/reindex_ca_crkc_sort_error.txt < /dev/null &
cd -