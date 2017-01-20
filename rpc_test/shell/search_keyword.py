#coding=utf-8
import MySQLdb
import string
conn = MySQLdb.connect(host="10.1.17.4",
                       user="search",
                       passwd="quite22-Gael",
                       port=6006,
                       db="search",
                       charset='utf8')
conn_tuanmei = MySQLdb.connect(host="10.0.18.106",
                       user="search",
                       passwd="quite22-Gael",
                       port=6006,
                       db="tuanmei")

#synonym_file_sql = "SELECT keyword,group_concat(map_word) FROM search.search_keyword_synonym group by keyword"
synonym_file_sql = "SELECT keyword,map_word FROM search.search_keyword_synonym"
brand_chinese_name_sql = "SELECT chinese_name FROM jumei_product.tuanmei_product_brands"
category_name_sql = "SELECT name FROM tuanmei.tuanmei_product_categories_v3"
function_name_sql = "SELECT name FROM tuanmei.tuanmei_product_functions "
pinyin_sql = "SELECT search_key FROM search.search_dict where search_value = '"


words_my_sql = "SELECT keyword FROM search_keyword_stop"

def sql_list(self,db):
    cursor = db.cursor()
    cursor.execute(self)
    res = cursor.fetchall()
    cursor.close()
    return res
def to_pinyin_single(chinese):
    cursor = conn.cursor()
    cursor.execute(pinyin_sql + chinese + "'")
    res = cursor.fetchone()
    cursor.close()
    return res
def to_pinyin(name):
    temp = ""
    for i in range(0,len(name)):
        if(ord(name[i]) > 0xa0):
            pinyin = to_pinyin_single(name[i:i+3])
            if pinyin:
                temp = name.replace(name[i:i+3],pinyin[0].encode("utf8"))
                break
            else:
                continue
    return temp
def to_pinyin_all(name):
    while to_pinyin(name) != "":
        name = to_pinyin(name)
    return name

if __name__ == '__main__':
    synonym_file = open("/home/jm/tomcat/webapps/search/search_jumei_com/conf/synonyms.txt", 'w')
    #dealman填写的同义词数据
    synonym = sql_list(synonym_file_sql, conn)
    arr = {}
    for i in synonym:
        #print i[0],i[1]
        for k, v in arr.iteritems():
            v_list = v.split(',')
            temp = v
            for word in v_list:
                if word == i[0]:
                    temp = v.replace(i[0], i[1])
            arr[k] = temp
            curr_list = i[1].split(',')
            for word in curr_list:
                if word == k:
                    i[1].replace(k,v)  
        arr[i[0]] = i[1]
    #print arr
    for k,v in arr.iteritems():
	#print v
        v_list = v.split(',')
        v_list_single = list(set(v_list))
        v_single = string.join(v_list_single, ',')
        keyword = u"%s => %s\n" %(k, v_single)
        keyword = keyword.encode('utf8')
        synonym_file.write(keyword)
	#print keyword
    # #将品牌转化成拼音
    #brand_chinese_name = sql_list(brand_chinese_name_sql,conn_tuanmei)
    #for name in brand_chinese_name:
    #    if name[0]:
    #        pinyin = to_pinyin_all(name[0])
    #        keyword = "".join(pinyin.lower().split()) + " => " + "".join(name[0].lower().split()) + '\n'
    #        synonym_file.write(keyword)
    # 	    print keyword
    #将分类转化成拼音
    #category_name = sql_list(category_name_sql,conn_tuanmei)
    #for name in category_name:
     #   if name[0]:
     #       pinyin = to_pinyin_all(name[0])
     #       keyword = "".join(pinyin.lower().split()) + " => " + "".join(name[0].lower().split()) + '\n'
     #       synonym_file.write(keyword)
    #function_name = sql_list(function_name_sql,conn_tuanmei)
    #for name in function_name:
    #    if name[0]:
    #        pinyin = to_pinyin_all(name[0])
    #        keyword = "".join(pinyin.lower().split()) + " => " + "".join(name[0].lower().split()) + '\n'
    #        synonym_file.write(keyword)
    synonym_file.close()

    #keyword words_my_sql
    words_my = sql_list(words_my_sql,conn)
    words_my_file = open('/home/jm/tomcat/webapps/search/search_jumei_com/conf/dict/words-my.dic','w')
    for my in words_my:
      keyword = u"%s\n" % (my[0])
      keyword = keyword.encode('utf8')
      keyword = keyword
      words_my_file.write(keyword)
    words_my_file.close()
conn.close()
