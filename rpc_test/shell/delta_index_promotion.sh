#!/usr/bin/env bash
#!/bin/bash
PATH=/usr/local/bin:/usr/bin:/bin:/usr/local/sbin:/usr/sbin:/sbin
export PATH

cd /home/www/jumei_search_index/ && php deltaIndex/PromotionDeltaIndexProcessor.php
if [ $? -ne 0 ]
then
echo -e  "</p>发生错误时间："
date "+%Y-%m-%d %H:%M:%S"
curl -s -d "menu=errorlog" -d email_destinations="shizhongl@jumei.com" -d email_subject="定时增量失败报告 [ `hostname -I` ]" -d email_content="`cat /dev/shm/delta_index_promotion.log`" http://email.int.jumei.com/send
fi