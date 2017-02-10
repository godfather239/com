#!/usr/bin/env python
# -*- coding: utf-8 -*-

import time
import config
import sys
from pyspark import SparkConf
from pyspark import SparkContext
from pyspark.sql import HiveContext
from pyspark.storagelevel import StorageLevel
from pyspark.sql.functions import row_number
from pyspark.sql.functions import desc
from pyspark.sql import Window
from config import CONFIG

def get_order_table(sparkContext, sqlContext):
    start = '2017-02-08'
    end = '2017-02-10'
    table = sqlContext.sql('''
          select sell_label,product_id as productId,sum(quantity*deal_price) as sales_amount from
          bi_datawarehouse.int_paid_orders where data_date >= '%s' AND data_date <= '%s'
                                and sell_label is not null and sell_label != ""
                                and sell_type = 'mSearch'
          group by sell_label,product_id
          order by sales_amount desc limit 100
    ''' % (start, end))
    table.persist(StorageLevel(True, True, False, False, 1))
    return table

def get_sensor_table(sparkContext, sqlContext):
    start = '2017-02-08'
    end = '2017-02-10'
    table = sqlContext.sql('''
        select search_word,product_id,sum(view) as exposure_count,sum(click) as click_count from
            (select
              case when (a.doc_type = 'global_mall' or a.doc_type = 'global_pop_mall') then b.product_id
                   else a.material_id
              end product_id,
              case when (a.event = 'view_material') then 1
                   else 0
              end view,
              case when (a.event = 'click_material') then 1
                   else 0
              end click,
              a.search_word
            from
            (select material_id,
                    regexp_extract(params, '^(.*?)&(.*?)$', 1) as search_word,
                    regexp_extract(material_id, '(.*p)?(\\d+).*',2) as material_id,
                    material_link,
                    regexp_extract(material_link, '^.*&type=(.*?)&.*', 1) as material_type from events
             where date >= '%s' and date <= '%s' and material_page='product_search_list' and params is not null
                  and (event = 'view_material' or event = 'click_material')
            ) a
                left join mysql.jumei_mall b on
             a.material_id = b.mall_id and (a.doc_type = 'global_mall' or a.doc_type = 'global_pop_mall')
             where case when (a.doc_type = 'global_mall' or a.doc_type = 'global_pop_mall') then b.product_id is not null
                        else a.material_id is not null
                   end and a.search_word is not null and a.search_word != '') t
            group by search_word,product_id
    ''' % (start, end))
    table.persist(StorageLevel(True, True, False, False, 1))
    return table

def write_to_hdfs(sqlContext, tableName):
    table = sqlContext.sql('''
        select concat(search_word, ',', concat_ws(',', collect_set(concat(product_id, '_', round(exposure_ecpm, 3))))) from
        %s GROUP BY search_word
    ''' % tableName)
    filename = time.strftime("%Y%m%d_%H%M%S", time.localtime())
    table.write.mode('overwrite').format('com.databricks.spark.csv').option("escape","\0").option("quoteMode", "NONE").\
          save('/hiveweb/recommend.db/ecpm/ecpm_%s' % filename)

def main():
    '''
    1. stat sensors log, output format: sell_label productId exposure_count
    2. stat order table, output format: search_word product_id sales_amount
    3. calculate ecpm, output format: search_word product_id exposure_ecpm click_ecpm
    :return:
    '''
    conf = SparkConf.setAppName("SearchECPMStat")
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
    table = table.witColumn('row_number', row_number().over(Window.partitionBy("search_word").orderBy(desc("exposure_ecpm")))).\
                  filter(table['row_number'] <= 100).\
                  drop('row_number')
    table.write.saveAsTable('ecpmstat.ecpm', mode = 'overwrite')

    write_to_hdfs(sqlContext, 'ecpmstat.ecpm')
    print ('ECPMStat finished!############################################')

if __name__ == "__main__":
    main()
