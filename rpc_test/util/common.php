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

function equal($arr, $key, $value) {
    if (!isset($arr) || !is_array($arr) || empty($arr)) {
        return false;
    }
    foreach ($arr as $item) {
        if (!isset($item[$key])) {
            return False;
        }
        $ret = mycomp($item[$key], $value, gettype($item[$key]));
        if (($ret === False) || ($ret != 0)) {
            return False;
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

