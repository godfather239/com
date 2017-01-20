namespace php Provider.Search_PopStore

service Search_PopStore
{
    map<i64,map<string,string>> getStoreListByPage(1:i64 page);
}