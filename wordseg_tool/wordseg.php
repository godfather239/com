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

/***************** Start here **************************/
$show_tag = (isset($argv[1]) && $argv[1] == 'show_tag');
while (($line = fgets(STDIN, 1024)) !== false) {
    $chars = array("\r", "\n");
    $encoded = rawurlencode(str_replace($chars, "", $line));
    $url = "http://localhost:8080/jms/keyword_jumei_com/qa?q={$encoded}&wt=json";
    $res = getCompressContent($url);
    if (isset($res['analysis'])) {
        $str = "";
        foreach ($res['analysis'] as $word => $postag) {
            if ($show_tag) {
                $str .= "{$word}/{$postag} ";
            } else {
                $str .= $word . " ";
            }
        }
        $str = substr($str, 0, -1);
        echo "{$str}\n";
    }
}
?>
