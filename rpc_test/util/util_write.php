<?php
class ImportWrite{
        
    
    /**
     * 
     * xml的&，<字符进行处理，
     * */
    public static function write_xml_add($data, $file) {
        $xml = '<add>';
        
        foreach ($data as $id => $product) {
            $xml.= '<doc>';
            foreach ($product as $product_attribute => $value) {
                if (is_array($value)) {
                    foreach ($value as $v) {
                        $xml .= self::gen_xml_for_one_attr($product_attribute, $v);
//                        $v=preg_replace("/[\\x00-\\x08\\x0b-\\x0c\\x0e-\\x1f]/", '', $v);
//                        $xml.= '<field name="' . str_replace("<","&lt;",str_replace("&","&amp;",$product_attribute)) . '">' . str_replace("<","&lt;",str_replace("&","&amp;",$v)) . '</field>';
                    }
                }else{
                    $xml .= self::gen_xml_for_one_attr($product_attribute, $value);
//                    $value=preg_replace("/[\\x00-\\x08\\x0b-\\x0c\\x0e-\\x1f]/", '', $value);
//                    $xml.= '<field name="' . str_replace("<","&lt;",str_replace("&","&amp;",$product_attribute)) . '">' . str_replace("<","&lt;",str_replace("&","&amp;",$value)) . '</field>';
                } 
            }
            $xml.= '</doc>';
        }
        
        $xml.= '</add>';
        if (file_put_contents($file, $xml, FILE_APPEND | LOCK_EX)) {
            return true;
        }
        
        return false;
    }

    private static function gen_xml_for_one_attr($attr_name, $attr_val) {
        $attr_val = preg_replace("/[\\x00-\\x08\\x0b-\\x0c\\x0e-\\x1f]/", '', $attr_val);
        $attr_name = str_replace("<","&lt;",str_replace("&","&amp;",$attr_name));
        if (preg_match('/(.*)\^boost:(.*)$/', $attr_val, $matches) == 1) {
            $attr_val = str_replace("<","&lt;",str_replace("&","&amp;",$matches[1]));
            $boost = $matches[2];
            $xml = "<field name=\"{$attr_name}\" boost=\"{$boost}\">{$attr_val}</field>";
        } else {
            $attr_val = str_replace("<","&lt;",str_replace("&","&amp;",$attr_val));
            $xml = "<field name=\"{$attr_name}\">{$attr_val}</field>";
        }
        return $xml;
    }
    
    public function gen_xml_head(){
        $xml = '<add>';
        return $xml;
    }
    
    public function gen_xml_del_body($type,$products){
        $delBody="";
        foreach ($products as $k => $v) {
            $delBody.="<delete><query>doc_type:{$type} AND product_id:{$v}</query></delete>";
        }
        return $delBody;
    }

    public function gen_xml_del_store_body($store_id){
        $delBody="";
        foreach ($store_id as $id) {
            $delBody.="<delete><query>id:{$id}</query></delete>";
        }
        return $delBody;
    }
    
    public static function get_xml_body($data){
        $body="";
        foreach ($data as $id => $product) {
            $body.= '<doc>';
            foreach ($product as $product_attribute => $value) {
                if (is_array($value)) {
                    foreach ($value as $v) {
                        $v=preg_replace("/[\\x00-\\x08\\x0b-\\x0c\\x0e-\\x1f]/", '', $v);
                        $body.= '<field name="' . str_replace("<","&lt;",str_replace("&","&amp;",$product_attribute)) . '">' . str_replace("<","&lt;",str_replace("&","&amp;",$v)) . '</field>';
                    }
                }else{
                    $value=preg_replace("/[\\x00-\\x08\\x0b-\\x0c\\x0e-\\x1f]/", '', $value);
                    $body.= '<field name="' . str_replace("<","&lt;",str_replace("&","&amp;",$product_attribute)) . '">' . str_replace("<","&lt;",str_replace("&","&amp;",$value)) . '</field>';
                }
            }
            $body.= '</doc>';
        }
        return $body;
    }


    public static function write_file(&$fp,$data){
        $xml = ImportWrite::get_xml_body($data);
        if (!empty($xml)) {
            fwrite($fp, $xml);
        }
    }


    public function gen_xml_foot(){
        $xml='<commit></commit></add>';
        return $xml;
    }
    
    public function gen_xml($body){
        return $this->gen_xml_head().$body.$this->gen_xml_foot();
    }
    
    public function update_timestamp($doc_type, $time) {
        global $CONFIG;
        $index_time = file_get_contents($CONFIG['file']['path']['data'] . "/data.properties");
        $last_time = array();
        if(!empty($index_time)){
            $tmp = explode("\n",$index_time);
            foreach ($tmp as $value) {
                $tmp2 = explode("=",$value);
                if($tmp2[0] == $doc_type){
                    $tmp2[1] = $time;
                }
                $last_time[$tmp2[0]] = $tmp2[0] . "=" .$tmp2[1];
            }
        }

        if(!isset($last_time[$doc_type])){
            $last_time[$doc_type] = "{$doc_type}=".$time;
        }
        file_put_contents($CONFIG['file']['path']['data'] . "/data.properties", implode("\n",$last_time));
    }
    
    static public function log_info($msg) {
        global $CONFIG;
        file_put_contents($CONFIG['log']['src_ids'], date("Y-m-d H:i:s")."--".$msg."\n", FILE_APPEND);
    }

    public static function write_log($log_type,$doc_type,$content,$log_path="",$log_level="info"){
        // 填充搜索日志
        $search_log = array (
            'time'=> date("Y-m-d H:i:s"),
            'log_type'=> $log_type,
            'doc_type'=>$doc_type,
            'log_level'=>$log_level,
            'content'=>$content,
        );
        file_put_contents($log_path,json_encode($search_log)."\n",FILE_APPEND);
    }
}
