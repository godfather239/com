<?php

class SolrUtil
{

    private $solrserver = '';

    private $core = '';

    /**
     *
     * @param string $core
     *            $multicore中的索引实例名称
     * @param string $solrserver
     *            solr服务器地址
     */
    public function __construct($solrserver, $core = null){
        $this->solrserver = $solrserver;
        if (isset($core) == true) {
            $this->core = $core;
        }
    }

    public function setCore($core){
        $this->core = $core;
    }

    public function getCore(){
        return $this->core;
    }

    public function update($datas){
        $method = '/update?wt=json';
//          file_put_contents("/home/logs/delta_import/log.txt",date("Y-m-d|H:i:s")."xml为：".$datas."\n",FILE_APPEND);
        $solrData = $this->httpPostXml($method, $datas);
        return $solrData;
    }

    private function httpPostXml($method,$data){
        $url = $this->solrserver ."/". $this->core . $method;;  //接收xml数据的文件
        $header[] = "Content-type: text/xml";        //定义content-type为xml,注意是数组
        $ch = curl_init ($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($ch);
       if(curl_errno($ch)){
           print curl_error($ch);
       }
       curl_close($ch);
        return $result;
    }


    public static function getCompressContent($url) {
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
            file_put_contents("/home/logs/index_excption.log", $e->getMessage().", rawurl:".$url, FILE_APPEND);
        }
    }
    
    
}

?>