#!/bin/bash
source /home/shell/conf.src
#/usr/bin/curl "http://${master_host}/search/search_jumei_com/dataimport?command=full-import" 2>&1 > /dev/null
/usr/bin/curl "http://${master_host}/search/suggestion_jumei_com/dataimport?command=full-import" 2>&1 > /dev/null
#/usr/bin/curl "http://${master_host}/search/history_jumei_com/dataimport?command=full-import" 2>&1 > /dev/null
/usr/bin/curl "http://${master_host}/search/pop_jumei_com/dataimport?command=full-import" 2>&1 > /dev/null
/usr/bin/curl "http://${master_host}/search/keyword_jumei_com/dataimport?command=full-import" 2>&1 > /dev/null
/usr/bin/curl "http://${master_host}/search/product_jumei_com/dataimport?command=full-import" 2>&1 > /dev/null
