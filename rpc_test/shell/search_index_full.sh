#!/bin/bash
PATH=/usr/local/bin:/usr/bin:/bin:/usr/local/sbin:/usr/sbin:/sbin
export PATH

echo "=======================    `date`    ========================="

set -x
source /home/shell/conf.src
>/home/logs/data_import/src.ids.log

# 回滚
rollback() {
	echo "=======================    `date`    ========================="
	curl -s -d "menu=errorlog" -d email_destinations="mengmengc@jumei.com" -d email_subject="[full_search_index] cron error from `hostname -I`" -d email_content="$1" http://email.int.jumei.com/send
	curl -s -d "menu=errorlog" -d email_destinations="qih1@jumei.com" -d email_subject="[full_search_index] cron error from `hostname -I`" -d email_content="$1" http://email.int.jumei.com/send
	curl -s -d "menu=errorlog" -d email_destinations="liangt@jumei.com" -d email_subject="[full_search_index] cron error from `hostname -I`" -d email_content="$1" http://email.int.jumei.com/send
        exit 1
}

#异常退出
exitProc() {
	echo "=======================    `date`    ========================="
	echo "xml生成失败"
	# 退出
	curl -s -d "menu=errorlog" -d email_destinations="liangt@jumei.com" -d email_subject="[full_search_index] cron error from `hostname -I`" -d email_content="$1" http://email.int.jumei.com/send
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
                        curl -s "http://${ip}:8080/search/search_jumei_com/replication?command=disablepoll&wt=json"
                        [ $? -eq 0 ] || echo "disablepoll ${ip} error"
                        status=$(curl -s "http://${ip}:8080/search/search_jumei_com/replication?command=details&wt=json"|grep -oP 'isPollingDisabled.+([a-z]+).+","'|grep 'true')
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
	                curl -s "http://${ip}:8080/search/search_jumei_com/replication?command=enablepoll&wt=json"
	                [ $? -eq 0 ] || echo "enablepoll ${ip} error"
	                status=$(curl -s "http://${ip}:8080/search/search_jumei_com/replication?command=details&wt=json"|grep -oP 'isPollingDisabled.+([a-z]+).+","'|grep 'false')
	                [ -n "$status" ] && break
	                sleep 1
	                i=$((i+1))
	        done
	done
}

mail_warning() {
    addrs="wenjieg@jumei.com"
    content=$1
    echo "${content}-`hostname -i`"|mail -s "full_index_error" ${addrs}
}

fetchdata() {
    types=( "global_mall" "global_deal" "mall_product" "deal" "pop" "pop_mall" "global_pop_mall")
    idx=0
    for type in ${types[@]}
    do
        php indexV2.php $type &
        pids[$idx]=`echo $!`
        ((idx++))
    done

    info=""
    succeed=0
    for ((i=0; i<idx; ++i))
    do
        wait ${pids[$i]}
        if [ $? -ne 0 ]; then
            info=${info}.","${types[$i]}
            succeed=1
        fi
    done

    if [ $succeed -ne 0 ];then
        echo "fetch data ${info} failed!"
        mail_warning $info
        return 1
    fi
    return 0
}

cd /home/www/jumei_search_index/ && fetchdata && {
	disablepoll
	[ $? -eq 0 ] && {
		# 备份索引
		[ -d /dev/shm/data.bak ] && rm -rf /dev/shm/data.bak
		[ -d /dev/shm/data ] && mv /dev/shm/data /dev/shm/data.bak
		# 删除主所有索引
		curl -s "http://${master_host}/search/search_jumei_com/update?stream.body=%3Cdelete%3E%3Cquery%3E*:*%3C/query%3E%3C/delete%3E&wt=json"
		[ $? -eq 0 ] || rollback "delete all search index error: $?"
		# 提交
		curl -s "http://${master_host}/search/search_jumei_com/update?stream.body=%3Ccommit/%3E&wt=json"
		[ $? -eq 0 ] || rollback "commit curl error: $?"
		numFound=$(curl -s  "http://${master_host}/search/search_jumei_com/select?q=*&wt=json" | grep -oP 'numFound":([0-9]),'|awk -F: '{print $2}'|sed 's#,##g')
		[ "$numFound" = "0" ] && {
			[ -f /home/data/data.properties ] && {
				cat /home/data/data.properties | awk -F'=' '{print $1"  "$2}' |while read p t
				do
					[ -z "${p}" -o -z "${t}" ] && continue
					f=$(echo "/home/data/${p}/${p}_full_import_${t}.xml" | tr -d "\r" )
					[ -f "${f}" ] && {
						[ `du -k "${f}" | awk '{print $1}'` -gt 1 ] && {
							json=$(curl -s "http://${master_host}/search/search_jumei_com/update?commit=true&wt=json" -H 'Content-Type: text/xml' --data-binary "@${f}")
							[ -n "`echo $json|grep -oP 'status":0,'`" ] || rollback "update $p error, file: $f, return : $json"
						} || rollback "update $p error, the file $f size less than 1k `cat /dev/shm/search_index_full.log`"
					} || rollback "file ${f} not found `cat /dev/shm/search_index_full.log`"
				done
				enablepoll || rollback "enablepoll error `cat /dev/shm/search_index_full.log`"
			} || rollback "data.properties not found"
		} || {
			rollback "commit return error: $numFound     `cat /dev/shm/search_index_full.log`"
		}
	} || rollback "disablepoll error"
} || exitProc "execute php scripts error: $? `cat /dev/shm/search_index_full.log`"

