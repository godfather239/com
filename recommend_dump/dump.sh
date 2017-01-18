#!/usr/bin/env bash

SPARK_DIR="/home/jm1/cdh/spark/bin"

######## Run sample program #################


# dump table data
tables=(
recommend.userOrderTable4T
#recommend.userTheta4T
#recommend.recommendFinalLogit4T
#recommend.userRecommen4T
#recommend.userCatg34T
#recommend.category3Similarity
#recommend.userCatg3Ext4T
#recommend.userBrandId4T
#recommend.brandIdSimilarity
#recommend.userBrandIdExt4T
#recommend.userCountry4T
#recommend.userCartTable4T
#recommend.userBrowseTable4T
#recommend.userLatestMonthOrder4T
#recommend.productTop100Table4T
)

mkdir -p output
rm -f output/*

for table in ${tables[@]}
do
    # Get table head TODO
    
    # Get rows
    sql="select '_gwj_',* from ${table}"
    filename="output/${table}.csv"
    >$filename
    ./spark-sql -e "${sql}" |grep "^_gwj_" >$filename
    sed -i "s/_gwj_\t//g" ${filename}
    sed -i "s/\t/,/g" ${filename}
done
