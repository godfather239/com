<?php
/**
 * Created by IntelliJ IDEA.
 * User: greenday
 * Date: 17-1-23
 * Time: 上午11:43
 */

function mycomp($lhs, $rhs, $type) {
    switch ($type) {
        case 'integer':
        case 'double':
            return $lhs-$rhs;
        case 'string':
            return strcmp($lhs, $rhs);
        default:
            return False;
    }
}

function greater($arr, $key, $value) {
    if (!isset($arr) || !is_array($arr) || empty($arr)) {
        return false;
    }
    foreach ($arr as $item) {
        if (!isset($item[$key])) {
            return False;
        }
        $ret = mycomp($item[$key], $value, gettype($item[$key]));
        if (($ret === False) || ($ret <= 0)) {
            return False;
        } 
    }
    return True;
}

/**
 * @param $arr
 * @param $key
 * @param $value 支持或关系表达,比如"a|b|c",表示数组元素匹配其中任何一个值,即为true
 * @param bool $skip_null
 * @return bool
 */
function equal($arr, $key, $value, $skip_null = true) {
    if (!isset($arr) || !is_array($arr) || empty($arr)) {
        return false;
    }
    foreach ($arr as $item) {
        if (!isset($item[$key]) && !$skip_null) {
            return False;
        }
        if (!isset($item[$key]) && $skip_null) {
            continue;
        }
        $sub_vals = explode("|", $value);
        $matched = false;
        foreach ($sub_vals as $val) {
            $ret = mycomp($item[$key], $val, gettype($item[$key]));
            if ($ret===0) {
                $matched = true;
                break;
            } 
        }
        if (!$matched) {
            return false;
        }
    }
    return True;
}

function lesser($arr, $key, $value) {
    if (!isset($arr) || !is_array($arr) || empty($arr)) {
        return false;
    }
    foreach ($arr as $item) {
        if (!isset($item[$key])) {
            return False;
        }
        $ret = mycomp($item[$key], $value, gettype($item[$key]));
        if (($ret === False) || ($ret >= 0)) {
            return False;
        }
    }
    return True;
}

