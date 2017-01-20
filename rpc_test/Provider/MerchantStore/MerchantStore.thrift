namespace php Provider.MerchantStore

/**
 * 服务说明 
 * 
 * @author
 * @copyright www.jumei.com 
 * 创建时间: 2016-4-22 13:58:30 
 */
service MerchantStore
{
    /**
     * 根据主播UID获取店铺链接.
     *
     * @param integer $uid 主播UID
     *
     * @return array.
     */
    map<string,string> getUrlByUid(1:i64 uid);
    
   /**
     * 通过专场店铺id获取店铺链接
     * @param $ids
     * @return array
     */
    map<i64,map<string,string>> getStoreLinkByStoreId(1:map<i64,i64> ids);
 
   map<string,string> getStoreInfoByStoreIds(1:map<i64,i64> ids);
    
}