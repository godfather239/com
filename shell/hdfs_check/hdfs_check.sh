#!/usr/bin/env bash

today=`date +%Y%m%d`
dir="hdfs:/hiveweb/recommend.db/homemain/homemainreco_${today}_*"
hadoop fs -ls $dir >/dev/null
if [ $? -ne 0 ];then
    echo "No such directory:$dir"
    for num in `echo "15600606592 15928730900 15202862569 18615757906"`
    do
        data="encript=false&global=0&key=bi_monitor_fb21522d19c17b54563f753155df5d38&channel=tencent&content=no recommend csv file of ${today}&num=${num}&task=what"
        curl -X POST -H 'Content-type:application/x-www-form-urlencoded' 'http://sms.int.jumei.com' -d "$data"
    done
fi
