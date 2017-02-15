#!/usr/bin/env python
# -*- coding: utf-8 -*-
import datetime

today = datetime.date.today()

CONFIG = {
    'today' : today,
    'stat_from' : today - datetime.timedelta(15),
    'stat_to' : today - datetime.timedelta(5),
    'table_suffix' : '_' + today.strftime("%Y_%m_%d"),
    'product_list_limit_per_query' : 100,
    'do_save_table' : True
}
