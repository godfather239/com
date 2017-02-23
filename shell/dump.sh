#!/usr/bin/env bash

while [ 1 ]
do
    echo "Hello world!"
    curl "https://www.charlesproxy.com/assets/release/4.0.2/charles-proxy-4.0.2.tar.gz" -o /home/greenday/Downloads/charles-proxy-4.0.2.tar.gz -C - --retry 3
    if [ $? -eq 0 ];then
        echo "dump finished!"
        exit 0
    fi
    sleep 1m
done

exit 0
