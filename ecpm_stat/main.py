#!/usr/bin/env python
# -*- coding: utf-8 -*-

import time
import sys
from pyspark import SparkConf
from pyspark import SparkContext
from pyspark.sql import HiveContext
from pyspark.storagelevel import StorageLevel
from pyspark.sql.functions import row_number
from pyspark.sql.functions import desc
from pyspark.sql import Window

def get_order_table(sparkContext, sqlContext):
    start = '2017-02-08'
    end = '2017-02-10'
    table = sqlContext.sql('''
          select sell_label,product_id as productId,sum(quantity*deal_price) as sales_amount from
          bi_datawarehouse.int_paid_orders where data_date >= '%s' AND data_date <= '%s'
                                and sell_label is not null and sell_label != ""
                                and sell_type = 'mSearch'
          group by sell_label,product_id
          order by sales_amount desc
    ''' % (start, end))
    table.persist(StorageLevel(True, True, False, False, 1))
    table.write.saveAsTable('recommend.ecpm_order', mode='overwrite')
    return table

def get_sensor_table(sparkContext, sqlContext):
    start = '20170208'
    end = '20170210'
    table = sqlContext.sql('''
        SELECT search_word,
               product_id,
               sum(VIEW) AS exposure_count,
               sum(click) AS click_count
        FROM
          (SELECT if(a.doc_type = 'global_mall' or a.doc_type = 'global_pop_mall', b.product_id, a.p_material_id)
                  AS product_id,
                  CASE
                      WHEN (a.event_id = 4) THEN 1
                      ELSE 0
                  END AS VIEW,
                  CASE
                      WHEN (a.event_id = 3) THEN 1
                      ELSE 0
                  END AS click,
                  a.search_word
           FROM
             (SELECT event_id,
                     search_word,
                     p_material_id,
                     p_material_link,
                     doc_type
              FROM
                (SELECT event_id,
                        regexp_extract(p_params, '^(.*?)&(.*?)$', 1) AS search_word,
                        regexp_extract(p_material_id, '(.*p)?(\\\d+).*',2) AS p_material_id,
                        p_material_link,
                        regexp_extract(p_material_link, '^.*&type=(.*?)&.*', 1) AS doc_type
                 FROM rawdata.event_ros_p1
                 WHERE DAY >= '%s'
                   AND DAY <= '%s'
                   AND p_material_page='product_search_list'
                   AND p_params IS NOT NULL
                   AND (event_id = 4
                        OR event_id = 3)) st
              WHERE search_word IS NOT NULL
                AND search_word != ''
                AND p_material_id IS NOT NULL
                AND p_material_id rlike '^\\\d+$' ) a
           LEFT JOIN mysql.jumei_mall b ON a.p_material_id = b.mall_id
           AND (a.doc_type = 'global_mall'
                OR a.doc_type = 'global_pop_mall')
           WHERE
                if(a.doc_type = 'global_mall' or a.doc_type = 'global_pop_mall', b.product_id, a.p_material_id) is not null
             AND a.search_word IS NOT NULL
             AND a.search_word != '' ) t
        WHERE product_id IS NOT NULL
        GROUP BY search_word,
                 product_id
    ''' % (start, end))
    table.persist(StorageLevel(True, True, False, False, 1))
    table.write.saveAsTable('recommend.ecpm_sensor', mode='overwrite')
    return table

def main():
    '''
    1. stat sensors log, output format: sell_label productId exposure_count
    2. stat order table, output format: search_word product_id sales_amount
    3. calculate ecpm, output format: search_word product_id exposure_ecpm click_ecpm
    '''
conf = SparkConf().setAppName("SearchECPMStat")
sc = SparkContext(conf = conf)
sqlContext = HiveContext(sc)
sc.setLogLevel("WARN")

# data clean
orderTable = get_order_table(sc, sqlContext)
sensorTable = get_sensor_table(sc, sqlContext)

cond = [orderTable['sell_label']==sensorTable['search_word'],orderTable['productId']==sensorTable['product_id']]
table = orderTable.join(sensorTable, cond, 'inner').\
                   withColumn('exposure_ecpm', orderTable.sales_amount / sensorTable.exposure_count * 100.0).\
                   withColumn('click_ecpm', orderTable.sales_amount / sensorTable.click_count * 100.0).\
                   select('search_word', 'product_id', 'exposure_ecpm', 'click_ecpm')
# result limit
table = table.withColumn('row_number', row_number().over(Window.partitionBy("search_word").orderBy(desc("exposure_ecpm"))))
table = table.filter(table['row_number'] <= 100).\
              drop('row_number')
table.write.saveAsTable('recommend.ecpm_final', mode = 'overwrite')

print ('ECPMStat finished!############################################')

if __name__ == "__main__":
    main()
