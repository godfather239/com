#!/usr/bin/env bash

source /home/shell/conf.src

function check()
{
    for ip in ${slave_host[@]}
    do
        status=`curl -s "http://${ip}:8080/search/search_jumei_com/replication?command=details&wt=json" |grep -oP 'isPollingDisabled.+([a-z]+).+","'`
	    echo "$ip    $status"
    done
}


function enable()
{
    for ip in ${slave_host[@]}
    do
        status=$(curl -s "http://${ip}:8080/search/search_jumei_com/replication?command=enablepoll&wt=json"|grep -oP 'isPollingDisabled.+([a-z]+).+","'|grep 'true')
    done
}

action=$1
case ${action} in
check)
check
;;
enable)
enable
;;
esac


exit 0