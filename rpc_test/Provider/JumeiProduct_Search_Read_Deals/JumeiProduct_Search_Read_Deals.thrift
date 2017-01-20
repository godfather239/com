namespace php Provider.JumeiProduct_Search_Read_Deals

/**
 * 服务说明 
 * 
 * @author
 * @copyright www.jumei.com 
 * 创建时间: 2015-7-10 14:44:26 
 */
service JumeiProduct_Search_Read_Deals
{
    /**
     * 根据海淘商品的hash_id数组获取商品详情(海淘自营和海淘pop).
     * 
     * @param array  $hashIdArr  HashId数组.
     *
     * @throws \RpcBusinessException 异常抛错.
     *
     * @return string
     */
    string getGlobalDealDetailForSearch(1:map<i64,string> hashIdArr)

    /**
     * 根据商品的hash_id数组返回特卖商品详情(主站自营).
     * 
     * @param array  $hashIdArr  HashId数组.
     *
     * @throws \RpcBusinessException 异常抛错.
     *
     * @return string
     */
    string getDealDetailForSearch(1:map<i64,string> hashIdArr)
    
   /**
     * 根据商品的hash_id数组返回特卖商品详情(主站商城).
     * 
     * @param array  $productIdArr  产品Id数组.
     *
     * @throws \RpcBusinessException 异常抛错.
     *
     * @return string
     */
    string getMallDetailForSearch(1:map<i64,string> productIdArr)

     /**
     * 根据pop的hash_id数组来获取pop商品的详情(主站pop).
     * 
     * @param array  $hashIdArr  HashId数组.
     *
     * @throws \RpcBusinessException 异常抛错.
     *
     * @return string
     */
    string getPopDetailForSearch(1:map<i64,string> hashIdArr)

    /**
     * 根据商品的hash_id数组返回特卖商品详情(海淘商城).
     * 
     * @param array  $productIdArr  产品Id数组.
     *
     * @throws \RpcBusinessException 异常抛错.
     *
     * @return string
     */
    string getGlobalMallDetailForSearch(1:map<i64,string> productIdArr)

    /**
     * 获取主站商城在售产品ID.
     * 
     * @param integer $minPid 查询条件中的最小产品ID.
     * @param integer $limit  限制条件.
     * 
     * @return string
     */
    string getPidOfJumeiMallByLimit(1:i64 minPid, 2:i64 limit)

    /**
     * 获取POP在售和即将开售的deal.
     * 
     * @param integer $minDealId 查询条件中最小deal_id.
     * @param integer $limit     限制条件.
     * 
     * @return string
     */
    string getHashIdOfPOPByLimit(1:i64 minDealId, 2:i64 limit)

 /**
     * 获取聚美自营的deal相关数据.
     * 
     * @param integer $minDealId 查询条件中最小deal_id.
     * @param integer $limit     限制条件.
     * 
     * @return string
     */
    string getJumeiDataWithMinDealIdAndLimit(1:i64 minDealId, 2:i64 limit)

 /**
     * 获取海淘商城的产品数据.
     * 
     * @param integer $minProductId 查询条件中最小product_id.
     * @param integer $limit     限制条件.
     * 
     * @return string
     */
    string getPOPMallProductIds(1:i64 minProductId, 2:i64 limit)

 /**
     * 获取POP商城的产品数据.
     * 
     * @param integer $minMallId 查询条件中最小mall_id.
     * @param integer $limit     限制条件.
     * 
     * @return string
     */
    string getMallDataWithMallIdAndLimit(1:i64 minMallId, 2:i64 limit)

    /**
     * 获取海淘deal相关数据.
     * 
     * @return string
     */
  string getGlobalDealDatas()

 /**
     * 获取海淘deal相关数据.
     * 
     * @param integer $minDealId 查询条件中最小deal_id.
     * @param integer $limit     限制条件.
     * 
     * @return string
     */
  string getGlobalDealDatasWithDealIdAndLimit(1:i64 minDealId, 2:i64 limit)

  /**
    * 获取海淘deal相关数据.
    * 
    * @return string
    */
  string getpPomoCardsDeal()
 
  /**
     * 获取主站POP商城数据.
     * 
     * @param array $productIds Product_id.
     * 
     * @return array
     */
  string getPopMallDetailForSearch(1:map<i64, i64> productIds)

  /**
     * 根据product_id获取Hash_id.
     * 
     * @param integer $product_id Product_id.
     * 
     * @return array
     */
  string getHashIdByProdcutId(1:i64 productIds)

   /**
     * 根据商品的product_id数组返回海淘POP商品详情(海淘POP商城).
     *
     * @param array $productIdArr 产品Id数组.
     *
     * @throws \RpcBusinessException 异常抛错.
     *
     * @return string
     */
    string getGlobalPOPMallDetailForSearch(1:map<i64,string> productIdArr)

   /**
     * 获取海淘商城的产品数据.
     *
     * @param integer $minMallId 查询条件中最小mall_id.
     * @param integer $limit     限制条件.
     *
     * @return string
     */
    string getGlobalPOPMallDataWithMallIdAndLimit(1:i64 minMallId, 2:i64 limit)

    /**
     * 获取POP在售和即将开售的deal.
     * 
     * @param integer minDealId 查询条件中最小deal_id.
     * @param integer limit     限制条件.
     * @param string  type      查询类型.
     * 
     * @return string
     */
    string getYQTByTypeLimit(1:i64 minDealId, 2:i64 limit, 3:string type)
}