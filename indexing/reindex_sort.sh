#!/bin/bash
cd support/bin
nohup php caUtils rebuild-sort-values  > /www/libis/web/lias_html/collectiveaccess/crkc_media/reindex_crkcsandbox_release_sort.log 2> /www/libis/web/lias_html/collectiveaccess/crkc_media/reindex_crkcsandbox_release_sort_error.txt < /dev/null &
cd -