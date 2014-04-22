#!/bin/bash
cd support/bin
nohup php caUtils rebuild-search-index  > /www/libis/web/lias_html/collectiveaccess/crkc_media/reindex_crkcsandbox_release.log 2> /www/libis/web/lias_html/collectiveaccess/crkc_media/reindex_crkcsandbox_release_error.txt < /dev/null &
cd -