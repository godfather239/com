namespace php Provider.Search_Activities

service Search_Activities
{
    map<i64,map<string,string>> getActivityListByPage(1:i64 page);
    map<string,map<string,map<i64,string>>> getDealAndProductActivityRelation(1:map<i64,string> hashIds, 2:map<i64,i64> productIds, 3:map<i64,i64> mallIds);
}