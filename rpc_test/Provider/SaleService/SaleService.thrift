namespace php Provider.SaleService

/**
 * 
 * @author 秦伟
 * @copyright www.jumei.com 
 * 创建时间: 2016-5-5 14:51:15 
 */
service SaleService
{
    /**
     * 销售额/销售量数据用于搜索结果排序
     *inputStr是一个json字符串， product_id数量不能超过100个，时间跨度不能大于30天，
     * user_tag是接口调用人信息方便我这边日志记录
     *String inputStr = "{product_id:[2542254,18525],star_date:\"2016-05-02\",end_date:\"2016-05-05\",user_tag:\"接口调用  
     *人\"}"
     */
    string getSaleInfo(1:string input)
}