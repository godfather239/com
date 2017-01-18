#!/usr/bin/env bash

echo "........."

function do_request() 
{
    uid=$1
    curl "http://search.int.jumei.com/rpctest/thrift/invoke" -H "Pragma: no-cache" -H "Origin: http://search.int.jumei.com" -H "Accept-Encoding: gzip, deflate" -H "Accept-Language: zh-CN,zh;q=0.8" -H "User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2783.4 Safari/537.36" -H "Content-Type: application/x-www-form-urlencoded; charset=UTF-8" -H "Accept: application/json, text/javascript, */*; q=0.01" -H "Cache-Control: no-cache" -H "X-Requested-With: XMLHttpRequest" -H "Cookie: PHPSESSID=23nke9h5an6kfvongoodfl30g7; first_visit=1; first_visit_time=1484633409; referer_site_cps=duomai__13722572; header_referer=""http://c.duomai.com/track.php?site_id=149169&aid=97&euid=&t=http"%"3A"%"2F"%"2Fitem.jumei.com"%"2Fdf1701102418p3187834.html""; cookie_uid=14846334293253097833; abt52=new; abt62=old; abt113=normal; abt114=normal; abt115=control; Hm_lvt_884477732c15fb2f2416fb892282394b=1484633431; Hm_lpvt_884477732c15fb2f2416fb892282394b=1484633431; user_history=eNp1UUFuwyAQ"%"2FErFObIBG0P8lVJZBHBMGwzCcIgs"%"2F72LeqgPyQV2Z2Znd2FHP"%"2Fa5oXFHZiYcE4JpT0TsiOCi69H4Gj4uyKis0Pi51"%"2Fp3sgvKz2gra9UDMq2yvYdU9d4apwCKKZii8"%"2BQMgP91JT0gX3KOo2xlG0Nsvou3rtHBy9bJtjrC"%"2BaJrs2Rfm21LSHlala8DyCLoMMvCek1kufazgvjG8AdcTFBZOO2AYFfKIb52GuIOM4LZcIcBCh8MiITQDJyN23Qoa7Zmisnp6s"%"2BGE17zhp6WSzbCLNu0Fn"%"2BzCWhc1bDBBOt4u54pThg6vuCt"%"2Fpze"%"2FQs5jl"%"2Bj3oNC; local_city_new="%"3Fsite"%"3Dcd"%"26city"%"3Dsichuan; local_city="%"7B"%"20"%"22site"%"22"%"3A"%"22cd"%"22"%"2C"%"22city"%"22"%"3A"%"22sichuan"%"22"%"20"%"7D; _adwc=265569940; _adwp=265569940.0393874731.1484633431.1484633431.1484633431.1; _adwr=265569940"%"23http"%"253A"%"252F"%"252Fc.duomai.com"%"252Ftrack.php"%"253Fsite_id"%"253D149169"%"2526aid"%"253D97"%"2526euid"%"253D"%"2526t"%"253Dhttp"%"25253A"%"25252F"%"25252Fitem.jumei.com"%"25252Fdf1701102418p3187834.html; __xsptplus428=428.1.1484633431.1484633431.1"%"233"%"7Cc.duomai.com"%"7C"%"7C"%"7C"%"7C"%"23"%"23q6MRUYDqoD3kI4KA9PDq2TB0a1VoYtrS"%"23; __utma=1.263432356.1484633432.1484633432.1484633432.1; __utmc=1; __utmz=1.1484633432.1.1.utmcsr=c.duomai.com|utmccn=(referral)|utmcmd=referral|utmcct=/track.php; ag_fid=A476bb3xJGiVpvgF; __ag_cm_=1484633431932; default_site_25=cd; JSESSIONID=74B411BB5A54C535D2FA6F3F9F3FDC13" -H "Connection: keep-alive" -H "Referer: http://search.int.jumei.com/rpctest/thrift/index?useLayout=false" --data "beanName=Client+"%"3A+recommendService&methodName=recommendForUser&params"%"5B"%"5D=java.lang.String"%"3A"%"257B"%"250A"%"2520"%"2520"%"2522uid"%"2522"%"253A"%"2520"%"2522${uid}"%"2522"%"252C"%"250A"%"2520"%"2520"%"2522size"%"2522"%"253A"%"2520"%"252210"%"2522"%"252C"%"250A"%"2520"%"2520"%"2522backup.model.enable"%"2522"%"253A"%"25201"%"252C"%"250A"%"2520"%"2520"%"2522locate"%"2522"%"253A"%"25200"%"252C"%"250A"%"2520"%"2520"%"2522locateDM"%"2522"%"253A"%"25201"%"252C"%"250A"%"2520"%"2520"%"2522model.timebasedrand.switch"%"2522"%"253A"%"25201"%"252C"%"250A"%"2520"%"2520"%"2522model.timebasedrand.cache.factors"%"2522"%"253A"%"25200"%"252C"%"250A"%"2520"%"2520"%"2522debug"%"2522"%"253A"%"25201"%"250A"%"257D" --compressed >/dev/null 2>&1

}

cat uid.txt |while read uid
do
    echo "$uid............."
    do_request $uid
done
#do_request "16323316"

exit 0


