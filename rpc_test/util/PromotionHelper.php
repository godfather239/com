<?php
require_once (PROJECT_ROOT . '/util/JMutil.php');
require_once (PROJECT_ROOT . '/util/SolrUtil.php');

/**
 *
 */
class PromotionHelper {
    
    private $promotion_detail;

    /*
     * 搜索步骤
     * 第0步: 按cat_id/brand_id分页获取
     * 第1步: 按product_id分页获取
     * 第2步: 按hash_id分页获取
     */
    private $max_request_param_num = 400;

    private $page_size = 1000;

    private $offset = 0;

    private $original_cur_index = 0;

    private $current_cur_index = 0;

    function PromotionHelper($promotion_detail) {
        $this->promotion_detail  = $promotion_detail;
        // 促销规则限定维度
        if(!empty($this->promotion_detail["productIds"])){
            $this->promotion_detail["productIds"] = explode(",", $this->promotion_detail["productIds"]);
            $this->promotion_detail["scope_type"] = "product";
        }else{
            $this->promotion_detail["brandIds"] = explode(",", $this->promotion_detail["brandIds"]);
            $this->promotion_detail["scope_type"] = "brand";
        }
        //　促销规则的booth转换成doc_type
        $doc_types = array();
        foreach(explode(",", $this->promotion_detail["booth"]) as $booth){
            switch($booth){
                case "mall":
                    $doc_types[] = "mall_product"; break;
                case "globalmall":
                    $doc_types[] = "global_mall"; break;
                case "mediamall":
                    $doc_types[] = "pop_mall"; break;
                case "deal":
                    $doc_types[] = "deal"; break;
                case "jumeiglobal":
                    $doc_types[] = "global_deal"; break;
                case "jumeiglobalmedia":
                    $doc_types[] = "global_deal"; break;
                case "media":
                    $doc_types[] = "pop"; break;
                case "globalmallmedia":
                    $doc_types[] = "global_pop_mall"; break;
            }
        }
        $this->promotion_detail["doc_types"] = $doc_types;
    }

    function fetchOriginalEffectProducts(){
        global $CONFIG;
        $solr_master = $CONFIG["server"]["solr_master"];

        $result = null;
        $promotion_id = $this->promotion_detail["ruleId"];

        $fq = $this->generateNamePairedFQ(array("promotion_rule_ids_mv_text_dynamic" => $promotion_id));
        $fl = rawurlencode("product_id,doc_type");
        $url = "http://{$solr_master}/search/search_jumei_com/select/?q=*:*&wt=json&fl={$fl}&fq=".rawurlencode($fq)."&rows=".$this->page_size."&start=".$this->original_cur_index;
        $this->original_cur_index += $this->page_size;

        $date = date("Ymd");
        file_put_contents("/home/logs/delta_import/promotion_delta_{$date}.log", date("Y-m-d|H:i:s")." fetchOriginalEffectProducts: {$url} \n",FILE_APPEND);

        $result = SolrUtil::getCompressContent($url);

        return $result["response"]["docs"];
    }

    function fetchCurrentEffectedProducts(){
        $result = null;
        switch($this->promotion_detail["scope_type"]){
            case "product":
                $result = $this->fetchByProductIds();
                break;
            case "brand" :
                $result = $this->fetchByBrandIds();
                break;
        }
        return $result;
    }

    function fetchByProductIds(){
        global $CONFIG;
        $solr_master = $CONFIG["server"]["solr_master"];

        $product_ids = $this->promotion_detail["productIds"];
        $doc_types = $this->promotion_detail["doc_types"];
        $sliced_product_ids = array_slice($product_ids, $this->offset, $this->max_request_param_num);
        $fq = $this->generateNamePairedFQ(array("product_id" => $sliced_product_ids, "doc_type" => $doc_types));
        $fl = rawurlencode("product_id,doc_type");
        $url = "http://${solr_master}/search/search_jumei_com/select/?q=*:*&wt=json&fl={$fl}&fq=".rawurlencode($fq)."&rows=".$this->page_size."&start=".$this->current_cur_index;
        $date = date("Ymd");
        file_put_contents("/home/logs/delta_import/promotion_delta_{$date}.log", date("Y-m-d|H:i:s")." fetchByProductIds: {$url} \n",FILE_APPEND);
        $result = SolrUtil::getCompressContent($url);

        $this->current_cur_index += $this->page_size;

        if(!isset($result["response"]) ) {
            return false;
        }

        $max_num_found = $result["response"]["numFound"];
        $products = $result["response"]["docs"];
        $products = $this->filteroutByExcept($products);

        if(sizeof($products) > 0){
            return $products;
        }else if($this->current_cur_index < $max_num_found){ //没有请求到数据,直接请求下一页
            return $this->fetchByProductIds();
        }else{
            return false;
        }
    }

    function fetchByBrandIds(){
        global $CONFIG;
        $solr_master = $CONFIG["server"]["solr_master"];

        $brandIds = $this->promotion_detail["brandIds"];
        $doc_types = $this->promotion_detail["doc_types"];
        $sliced_ids = array_slice($brandIds, $this->offset, $this->max_request_param_num);
        $fq = $this->generateNamePairedFQ(array("brand_id" => $sliced_ids, "doc_type" => $doc_types));
        $fl = rawurlencode("product_id,doc_type");
        $url = "http://{$solr_master}/search/search_jumei_com/select/?q=*:*&wt=json&fl={$fl}&fq=".rawurlencode($fq)."&rows=".$this->page_size."&start=".$this->current_cur_index;
        $date = date("Ymd");
        file_put_contents("/home/logs/delta_import/promotion_delta_{$date}.log", date("Y-m-d|H:i:s")." fetchByBrandIds: {$url} \n",FILE_APPEND);
        $result = SolrUtil::getCompressContent($url);
        $this->current_cur_index += $this->page_size;

        if(!isset($result["response"]) ) {
            return false;
        }

        $max_num_found = $result["response"]["numFound"];
        $products = $result["response"]["docs"];
        $products = $this->filteroutByExcept($products);
        if(sizeof($products) > 0){
            return $products;
        }else if($this->current_cur_index < $max_num_found){ //没有请求到数据,直接请求下一页
            return $this->fetchByProductIds();
        }else{
            return false;
        }
    }

    /**
     * @param $productArray
     * @return mixed
     */
    private function filteroutByExcept($productArray){
        return $productArray;
    }

    private function generateNamePairedFQ($conditions){
        $fq = null;
        foreach($conditions as $key => $values) {

            if(is_array($values)){
                $fq .= "( ${key}:" . implode(" OR ${key}:", $values) . " )";
            }else{
                $fq .= "( ${key}:${values} )";
            }

            $fq .= " AND ";
        }
        if(!empty($fq)){
            $fq = substr_replace($fq, ' ', -4);
        }
        return $fq;
    }
}