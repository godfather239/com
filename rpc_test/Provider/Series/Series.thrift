namespace php Provider.Series

/**
 * 服务说明 
 * 
 * @author
 * @copyright www.jumei.com 
 * 创建时间: 2015-11-10 17:30:57 
 */
service Series
{
    /**
     * 方法说明
     * 
     */
    map<i64,map<i64,map<i64, map<string,string>>>> getSeriesInfoByProductId(1:map<i32,i64> productIds, 2:i64 skipCache);
    map<i64,map<i64,map<i64, map<string,string>>>> getSeriesInfoByProductAndStore(1:map<i32,map<i32,i64>> filter, 2:i64 skipCache);
    string getSeriesById(1:i64 sid);
}