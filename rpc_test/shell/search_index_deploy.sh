#!/bin/bash
PATH=/usr/local/bin:/usr/bin:/bin:/usr/local/sbin:/usr/sbin:/sbin
export PATH

echo "=======================    `date`    ========================="

set -x
source /home/shell/conf.src


# 回滚
rollback() {
	echo "=======================    `date`    ========================="
	curl -s -d "menu=errorlog" -d email_destinations="mengmengc@jumei.com" -d email_subject="[full_search_index] cron error from `hostname -I`" -d email_content="$1" http://email.int.jumei.com/send
	curl -s -d "menu=errorlog" -d email_destinations="qih1@jumei.com" -d email_subject="[full_search_index] cron error from `hostname -I`" -d email_content="$1" http://email.int.jumei.com/send
	curl -s -d "menu=errorlog" -d email_destinations="liangt@jumei.com" -d email_subject="[full_search_index] cron error from `hostname -I`" -d email_content="$1" http://email.int.jumei.com/send
        exit 1
}

# 隔离主从同步

disablepoll() {
	echo "发布时使用发布python进行主从控制"
}


# 恢复主从同步
enablepoll() {
	echo "发布时使用发布python恢复主从控制"
}


cd /home/www/jumei_search_index/ && php index.php && {
	disablepoll
	[ $? -eq 0 ] && {
		# 备份索引
		# 发布不进行索引备份，否则会备份为空
		#[ -d /dev/shm/data.bak ] && rm -rf /dev/shm/data.bak
		#[ -d /dev/shm/data ] && mv /dev/shm/data /dev/shm/data.bak
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
						} || rollback "update $p error, the file $f size less than 1k `cat /dev/shm/search_deploy.log`"
					} || rollback "file ${f} not found `cat /dev/shm/search_deploy.log`"
				done
				enablepoll || rollback "enablepoll error `cat /dev/shm/search_deploy.log`"
			} || rollback "data.properties not found"
		} || {
			rollback "commit return error: $numFound     `cat /dev/shm/search_deploy.log`"
		}
	} || rollback "disablepoll error"
} || rollback "execute php scripts error: $? `cat /dev/shm/search_deploy.log`"

