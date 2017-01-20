#!/usr/bin/env bash

PATH=/usr/local/bin:/usr/bin:/bin:/usr/local/sbin:/usr/sbin:/sbin
export PATH

cd /home/www/jumei_search_index/
php deltaIndex/ProductDeltaIndex.php $@

if [ $? -ne 0 ];then
    echo -e  "</p>发生错误时间："
    date "+%Y-%m-%d %H:%M:%S"
    curl -s -d "menu=errorlog" -d email_destinations="shizhongl@jumei.com" -d email_subject="定时增量失败报告 [ `hostname -I` ]"
        -d email_content="`cat /dev/shm/delta_index_$@.log`" http://email.int.jumei.com/send
fi
echo "$@ delta index success!"

#if [ "${data_type}" = "deal" ]; then
#    php deltaIndex/JumeiDealDeltaIndex.php
#elif [ "${data_type}" = "mall_product" ];then
#    php deltaIndex/JumeiMallDeltaIndex.php
#elif [ "${data_type}" = "global_deal" ];then
#    php deltaIndex/GlobalDealDeltaIndex.php
#elif [ "${data_type}" = "global_mall" ];then
#    php deltaIndex/GlobalMallDeltaIndex.php
#elif [ "${data_type}" = "pop" ];then
#    php deltaIndex/PopDealDeltaIndex.php
#elif [ "${data_type}" = "pop_mall" ];then
#    php deltaIndex/PopMallDeltaIndex.php
#elif [ "${data_type}" = "global_pop_mall" ];then
#    php deltaIndex/GlobalPopMallDeltaIndex.php
#elif [ "${data_type}" = "promotion" ];then
#    php deltaIndex/PromotionDeltaIndexProcessor.php
#else
#    echo "Invalid data type!"
#    exit 1
#fi
