#!/bin/bash
PATH=/usr/local/bin:/usr/bin:/bin:/usr/local/sbin:/usr/sbin:/sbin
export PATH

echo "=======================    `date`    ========================="

set -x
source /home/shell/conf.src

# 回滚
rollback() {
	echo "=======================    `date`    ========================="
	# 回滚
	curl -s -d "menu=errorlog" -d email_destinations="liangt@jumei.com" -d email_subject="[full_activity_index] cron error from `hostname -I`" -d email_content="$1" http://email.int.jumei.com/send
        exit 1
}

# 隔离主从同步
disablepoll() {
	echo "=======================    `date`    ========================="
	for ip in ${slave_host[@]}
        do
                i=0
                while [ : ]
                do
                        [ $i -eq 10 ] && rollback "disablepoll ${ip} timeout"
                        curl -s "http://${ip}:8080/search/activities_jumei_com/replication?command=disablepoll&wt=json"
                        [ $? -eq 0 ] || echo "disablepoll ${ip} error"
                        status=$(curl -s "http://${ip}:8080/search/activities_jumei_com/replication?command=details&wt=json"|grep -oP 'isPollingDisabled.+([a-z]+).+","'|grep 'true')
                        [ -n "$status" ] && break
                        sleep 1
                        i=$((i+1))
                done
        done
}


# 恢复主从同步
enablepoll() {
	echo "=======================    `date`    ========================="
	for ip in ${slave_host[@]}
	do
	        i=0
	        while [ : ]
	        do
	                [ $i -eq 10 ] && rollback "enablepoll ${ip} timeout"
	                curl -s "http://${ip}:8080/search/activities_jumei_com/replication?command=enablepoll&wt=json"
	                [ $? -eq 0 ] || echo "enablepoll ${ip} error"
	                status=$(curl -s "http://${ip}:8080/search/activities_jumei_com/replication?command=details&wt=json"|grep -oP 'isPollingDisabled.+([a-z]+).+","'|grep 'false')
	                [ -n "$status" ] && break
	                sleep 1
	                i=$((i+1))
	        done
	done
}


cd /home/www/jumei_search_index/ && php activities.php && {
	disablepoll
	[ $? -eq 0 ] && {
		# 备份索引
		# 删除主所有索引
		curl -s "http://${master_host}/search/activities_jumei_com/update?stream.body=%3Cdelete%3E%3Cquery%3E*:*%3C/query%3E%3C/delete%3E&wt=json"
		[ $? -eq 0 ] || rollback "delete all search index error: $?"
		# 提交
		curl -s "http://${master_host}/search/activities_jumei_com/update?stream.body=%3Ccommit/%3E&wt=json"
		[ $? -eq 0 ] || rollback "commit curl error: $?"
		numFound=$(curl -s  "http://${master_host}/search/activities_jumei_com/select?q=*&wt=json" | grep -oP 'numFound":([0-9]),'|awk -F: '{print $2}'|sed 's#,##g')
		[ "$numFound" = "0" ] && {
			[ -f /home/data/activity.properties ] && {
				cat /home/data/activity.properties | awk -F'=' '{print $1"  "$2}' |while read p t
				do
					[ -z "${p}" -o -z "${t}" ] && continue
					f=$(echo "/home/data/${p}/${p}_full_import_${t}.xml" | tr -d "\r" )
					[ -f "${f}" ] && {
						[ `du -k "${f}" | awk '{print $1}'` -gt 1 ] && {
							json=$(curl -s "http://${master_host}/search/activities_jumei_com/update?commit=true&wt=json" -H 'Content-Type: text/xml' --data-binary "@${f}")
							[ -n "`echo $json|grep -oP 'status":0,'`" ] || rollback "update $p error, file: $f, return : $json"
						} || rollback "update $p error, the file $f size less than 1k `cat /dev/shm/activity_index_full.log`"
					} || rollback "file ${f} not found `cat /dev/shm/activity_index_full.log`"
				done
				enablepoll || rollback "enablepoll error `cat /dev/shm/activity_index_full.log`"
			} || rollback "activity.properties not found"
		} || {
			rollback "commit return error: $numFound     `cat /dev/shm/activity_index_full.log`"
		}
	} || rollback "disablepoll error"
} || rollback "execute php scripts error: $? `cat /dev/shm/activity_index_full.log`"

