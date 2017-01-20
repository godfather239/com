<?php
/**
 * Created by IntelliJ IDEA.
 * User: greenday
 * Date: 17-1-20
 * Time: 下午6:33
 * Brief: Easy lexer for rpc test check using preg replace
 */

class EasyLexer {
    static public function parse($str, $obj) {
        // Replace dynamic pattern
//        preg_match('/\$[a-zA-Z_]+/', $str, $matches);
        $count = preg_match_all('/\$([a-zA-Z_]+\.?)+/', $str, $matches);
        if ($count == False) {
            return $str;
        }
        var_dump($matches);
        $matches = $matches[0];
        for ($i = 1; $i < count($matches); ++$i) {
            $arr = explode(".", $matches[$i]);
            $str = "";
            foreach ($arr as $idx => $node) {
                if (strpos($node, "$") === 0) {
                    $val = str_replace("$", "", $node);     
                    
                }
            }
        }
//        $matches = $matches[1];
//        for ($i = 1; $i < count($matches); ++$i) {
//            $val = str_replace("$", "", $matches[$i]);
//            echo $val."\n";
//            $pattern = '/\$' . $val . '/';
//            if (isset($obj[$val])) {
//                $str = preg_replace($pattern, $obj[$val], $str);
//            }
//        }
        return $str;
        // TODO
    }
}

$obj = array('search'=>'面膜');
echo EasyLexer::parse('count($search.data.rows)>=0&&$search.data.pageNumber=1', $obj)."\n";