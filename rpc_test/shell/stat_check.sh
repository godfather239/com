#!/bin/sh

# 检查redis状态，如果出现异常，则打印日志并发送邮件

##
# Send mail to rd
# @param    mail subject
# @param    mail content
##
send_mail()
{
    subject=$1
    content=$2
    addrs="wenjieg@jumei.com"
	#curl -s -d "menu=errorlog" -d email_destinations="${addrs}" -d email_subject="${subject}" \
	#-d email_content="${content}" http://email.int.jumei.com/send
	echo ${content}|mail -s ${subject} ${addrs}
}


# 1.检查是否能够写入
ret=`redis-cli set redis_stat "stat_check"`
if [ -z $ret -o $ret != "OK" ]; then
    echo "stat_check failed"
    subject="[redis_stat_check][error]--host:`hostname -I`"
    content="Couldn't write to redis!"
    send_mail ${subject} ${content}
    return 1
fi

echo "stat check finished. everything is OK"
exit 0
