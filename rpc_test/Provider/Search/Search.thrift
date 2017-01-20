namespace php Provider.Search

    service Search
    {

        /**
        * 第四版取搜索数据，商品包含海淘组套商品。
        **/
        string getSearchData_v4(1:string filter_array);
        /**
        * 搜索框下面的关键词联想
        **/
        string getSuggestionKeywords(1:string input);
        /**
        * 根据filter查询pop商品列表。
        **/
        string getPopList(1:string filter_array);
        /**
        * 获取对应平台的热搜词
        **/
        map<string,string> getSearchHotKeywords(1:i64 count,2:string type);
        /**
        * query解析
        **/
        string queryAnalyse(1:string query);
        /**
        *
        **/
        string queryAnalyse_v2(1:string query,2:string sort_type);
        /**
        * 商品的短名称查询，optool那边需要用到
        **/
        string getProductByName(1:string product_short_name,2:i64 count);
        string getAllBrotherCats(1:string filter_array);
        string getSeriesNumfound(1:map<i32,i64> series_id,2:string site);
        string getBrandID(1:string brand_friend_name);
        string getDefaultwords();
        string getSearchHotwords();
        string getFieldFacet(1:string filter_array)
    }

