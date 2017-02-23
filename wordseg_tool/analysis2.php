<?php

function getCompressContent($url) {
    try{
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
        $output = curl_exec($ch);
        $output = json_decode($output, 1);
        curl_close($ch);
        return $output;
    }catch(\Exception $e){
        var_dump($e->getMessage());
        return false;
    }
}

$i = 0;
while (($line = fgets(STDIN, 10240)) !== false) {
    
    $chars = array("\n");
    $line = str_replace($chars, "", $line);
    //echo "{$line}\n";
    //$encoded = rawurlencode(str_replace($chars, "", $line));
    $arr = explode("&", $line);
    
    $res = array();
    foreach ($arr as $idx => $item) {
        $value = explode("=", $item);
        $res[$value[0]] = rawurlencode($value[1]);
    }

    // Generate url
    $search_url = "http://solr.pub.jumei.com/search/search_jumei_com/edismax/?";
    foreach ($res as $key => $value) {
        $search_url .= $key . "=" . $value . "&";
    }
    //echo $search_url."\n";

    // send http request
    $res = getCompressContent($search_url);
    //echo json_encode($res)."\n";
    //var_dump($res);

    //#$url = "http://localhost:8080/jms/search_jumei_com/analysis/field?analysis.fieldvalue={$encoded}&analysis.fieldname=text&wt=json";
    ////$search_url = "localhost:8080/jms/";
    //$url = "http://{$search_url}/store_jumei_com/analysis/field?analysis.fieldvalue={$encoded}&analysis.fieldname=store_name&wt=json";
    //$url = "http://{$search_url}/search_jumei_com/edismax?q={$arr['q']}&fq={$arr['fq']}&sort={$arr['sort']}&wt={$arr['wt']}&qf={$arr['qf']}&bf={$arr['qf']}";
    //$res = getCompressContent($url);
    //echo json_encode($res)."\n";
    #var_dump($res);
    //if (isset($res['analysis']['field_names'])) {
        //$arr = $res['analysis']['field_names']['store_name']['index'];
        //$str = str_replace($chars, "", $line) . "\t";
        //foreach ($arr as $idx => $val) {
            //if ($idx != 7) {
                //continue;
            //}
            //foreach ($val as $i => $item) {
                ////echo json_encode($item)."\n";
                //$str .= $item['text'] . "/";
            //}
        //}
        //$str = substr($str, 0, -1);
        //echo "{$str}\n";
    //}
}

?>
