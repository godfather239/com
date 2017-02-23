<?php

while (($line=fgets(STDIN))!=false) {
    $line = str_replace("\n","", $line);
    $val = json_decode($line, true);
    if (isset($val['search_keywords']) and !empty($val['search_keywords'])) {
        echo $val['search_keywords']."\n";
    }
}

?>
