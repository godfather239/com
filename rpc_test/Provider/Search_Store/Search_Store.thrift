namespace php Provider.Search_Store

/**
 * 服务说明 
 * 
 * @author
 * @copyright www.jumei.com 
 * 创建时间: 2015-12-22 10:12:20 
 */
service Search_Store
{
    /**
     * 方法说明
     * 
     */
  string getSearchStore(1:string filter_array);
    
  /**
     * 方法说明
     * 
     */
  string getSearchStore_v2(1:string filter_array);
    
  /**
     * 方法说明
     *
     */
  string getSearchStore_v3(1:string filter_array);
}