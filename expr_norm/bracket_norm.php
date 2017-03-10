<?php
    while (($line = fgets(STDIN, 10000)) != false) {
        $tabcnt = 0;
        $start = 0;
        $sep = "  ";
        $delims = array();
        for ($i = 0; $i < strlen($line); ++$i) {
            switch ($line[$i]) {
                case '(':
                    $str = substr($line, $start, $i-$start+1);
                    print implode($delims).$str."\n";
                    array_push($delims, $sep);
                    $start = $i+1;
                    break;
                case ')':
                    print implode($delims).substr($line, $start, $i-$start)."\n";
                    array_pop($delims);
                    print implode($delims).")\n";
                    $start = $i+1;
                    break;
                default:
                    break;
            }
        }
    }
?>
