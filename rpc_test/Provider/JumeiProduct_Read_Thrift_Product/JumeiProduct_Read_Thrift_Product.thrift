namespace php Provider.JumeiProduct_Read_Thrift_Product

/**
 * 服务说明 
 * 
 * @author
 * @copyright www.jumei.com 
 * 创建时间: 2015-3-10 14:50:11 
 */
service JumeiProduct_Read_Thrift_Product
{
    /**
     * 返回sku是否自主品牌,商家名称,商品名称,品牌名称.
     * 
     * @param string $params 参数.
     * 
     * @return string
     * 
     * @throws \RpcBusinessException 参数有误.
     */
    string getProductInfo(1:string params);

    /**
     * 返回sku是所有的商城商品和在售deals.
     * 
     * @param string $sku 参数.
     * 
     * @return string
     * 
     * @throws \RpcBusinessException 参数有误.
     */
    map<string,map<string,string>> getDealProductBySku(1:string params);
}