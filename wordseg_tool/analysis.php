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

while (($line = fgets(STDIN, 1024)) !== false) {
    $chars = array("\r", "\n");
    $encoded = rawurlencode(str_replace($chars, "", $line));
    #$url = "http://localhost:8080/jms/search_jumei_com/analysis/field?analysis.fieldvalue={$encoded}&analysis.fieldname=text&wt=json";
    $search_url = "localhost:8080/jms/";
    //$search_url = "product-solr.int.jumei.com/search/";
    $url = "http://{$search_url}/store_jumei_com/analysis/field?analysis.fieldvalue={$encoded}&analysis.fieldname=store_name&wt=json";
    //$url = "http://{$search_url}/activities_jumei_com/analysis/field?q={$encoded}&analysis.fieldname=text&wt=json";
    $res = getCompressContent($url);
    #var_dump($res);
    if (isset($res['analysis']['field_names'])) {
        $arr = $res['analysis']['field_names']['store_name']['index'];
        $str = str_replace($chars, "", $line) . "\t";
        foreach ($arr as $idx => $val) {
            if ($idx != 7) {
                continue;
            }
            foreach ($val as $i => $item) {
                //echo json_encode($item)."\n";
                $str .= $item['text'] . "/";
            }
        }
        $str = substr($str, 0, -1);
        echo "{$str}\n";
    }
}

?>
