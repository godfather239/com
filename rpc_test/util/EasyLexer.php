<?php
/**
 * Created by IntelliJ IDEA.
 * User: greenday
 * Date: 17-1-20
 * Time: 下午6:33
 * Brief: Easy lexer for rpc test check using preg replace
 */

class EasyLexer {
    /**
     * @brief expression format: $search.data.rows.0.doc_type=='global_mall'&&$search.data.pageNumber>=1
     * @param $str
     * @param $obj
     * @param $res_name
     * @return mixed
     */
    static public function parse($str, $obj, $res_name) {
        // Replace dynamic pattern
        $count = preg_match_all('/{\$?([a-zA-Z0-9_]+\.?)+}/', $str, $matches);
        if ($count == False) {
            return $str;
        }
        $matches = $matches[0];
        for ($i = 0; $i < count($matches); ++$i) {
            $arr = explode(".", $matches[$i]);
            foreach ($arr as $idx => $node) {
                if (strpos($node, "$") === 0) {
                    $val = str_replace("$", "", $node);     
                    if (isset($obj[$val])) {
                        $arr[$idx] = $obj[$val];
                    }
                }
            }
            $str = str_replace($matches[$i], self::genEvalStr($arr, $res_name), $str); 
        }
        return $str;
    }
    
    static private function genEvalStr($arr, $res_name) {
        $str = "$".$res_name;
        foreach ($arr as $sub_node) {
            $str .= "['".$sub_node."']";
        }
        return $str;
    }
}

