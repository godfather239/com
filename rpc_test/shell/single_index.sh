#!/bin/bash -x
# Index for one doc_type
source /home/shell/conf.src

if [ $# != 1 ];then
    echo "USAGE: ./single_index.sh <doc_type>"
    exit 1
fi

# 1. dump data
# 2. delete index in solr
# 3. update date to solr
doc_type=$1
cd /home/www/jumei_search_index && php indexV2.php ${doc_type}
if [ $? -ne 0 ];then
    echo "Dump data failed!"
    exit 2
fi

timestamp=`grep "^${doc_type}=" /home/data/data.properties |cut -d'=' -f2`
if [ -z "${timestamp}" ];then
    echo "Fetch timestamp of ${doc_type} failed!"
    exit 4
fi
echo "timestamp: ${timestamp}"

data_file="/home/data/${doc_type}/${doc_type}_full_import_${timestamp}.xml"

curl -s "http://${master_host}/search/search_jumei_com/update?stream.body=%3Cdelete%3E%3Cquery%3Edoc_type%3A${doc_type}%3C%2Fquery%3E%3C%2Fdelete%3E&wt=json&commit=true"
if [ $? -ne 0 ];then
    echo "Delete index of ${doc_type} failed!"
    exit 3
fi


curl -s "http://${master_host}/search/search_jumei_com/update?commit=true&wt=json" -H 'Content-Type: text/xml' --data-binary "@${data_file}"
if [ $? -ne 0 ];then
    echo "Update data of ${doc_type} to solr failed!"
    exit 5
fi

echo "index succeed!"
exit 0

