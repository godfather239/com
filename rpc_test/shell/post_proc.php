<?php
function match_numeric_dyt($name) {
    $numeric_dyt = array("_float_dynamic",
        "_float_mv_dynamic" => 1,
        "_long_dynamic" => 1,
        "_long_mv_dynamic" => 1,
        "_int_dynamic" => 1,
        "_int_mv_dynamic" => 1);

    foreach ($numeric_dyt as $type => $val) {
        if (strpos($name, $type) !== false) {
            return true;
        }
    }
    return false;
}

function get_main_key($filepath) {
    $arr = split("/", $filepath);
    $filepath = $arr[count($arr) - 1];
    var_dump($filepath);
    if ((strpos($filepath, 'deal_full') !== false) ||
        (strpos($filepath, 'pop_full') !== false) ||
        (strpos($filepath, 'global_deal_full') !== false)) {
        return 'hash_id';
    }
    if ((strpos($filepath, 'global_mall_full') !== false) ||
        (strpos($filepath, 'global_pop_mall_full') !== false)) {
        return 'mall_id';
    }
    return 'product_id';
}

if(!isset($argv) || empty($argv) || $argc < 2){
    echo "invalid args\n";
    exit(1);
}
$filepath = $argv[1];
echo "filepath:{$filepath}\n";
$main_key = get_main_key($filepath);


$reader = new XMLReader();
if (!$reader->open($filepath, 'utf-8')) {
    echo "Failed to open file {$filepath}\n";
    exit(2);
}

$numeric_stt = array(
    "series_id" => 1,
    "area_code" => 1,
    "area_exchange_rate" => 1,
    "area_currency_symbol_location" => 1,
    "abroad_price" => 1,
    "is_sellable" => 1,
    "shipping_system_id" => 1,
    "package_price" => 1,
    "package_id" => 1,
    "deal_real_buyer_number" => 1,
    "deal_id" => 1,
    "start_time" => 1,
    "end_time" => 1,
    "buyer_number" => 1,
    "mall_real_buyer_number" => 1,
    "real_30day_buyer_number" => 1,
    "fake_30day_buyer_number" => 1,
    "real_30day_sales_amount" => 1,
    "sort_sales_volume" => 1,
    "sort_sales_amount" => 1,
    "sort_price" => 1,
    "sort_start_date" => 1,
    "sort_popularity" => 1,
    "sale_price" => 1,
    "sale_start_time" => 1,
    "sale_end_time" => 1,
    "show_status" => 1,
    "mall_id" => 1,
    "brand_id" => 1,
    "is_authorization" => 1,
    "function_id" => 1,
    "category_id_1" => 1,
    "category_id_2" => 1,
    "category_id_3" => 1,
    "category_id_4" => 1,
    "product_reports_number" => 1,
    "product_report_rating" => 1,
    "deal_comments_number" => 1,
    "is_available_bj" => 1,
    "is_available_cd" => 1,
    "is_available_gz" => 1,
    "is_available_sh" => 1,
    "countries" => 1,
    "merchant_id" => 1,
    "wish_number" => 1,
    "is_published_price" => 1,
    "high_priority_sort" => 1,
    "deal_sort" => 1,
    "sale_amount" => 1,
    "store_id" => 1,
    "activityId" => 1,
    "saved_amount" => 1,
    "payment_start_time" => 1,
    "payment_end_time" => 1,
    "is_new_tag_time" => 1
);

$filter_fields = array(
);

$docs = array();
while ($reader->read()) {
    if ($reader->name == 'doc' && $reader->nodeType == XMLReader::ELEMENT) {
        // Read a doc
        $doc = array();
        $doc_valid = true;
        while ($reader->read()) {
            if ($reader->name == 'doc') {
                break;
            } else if ($reader->name == 'field') {
                // Read one field
                $name = $reader->getAttribute('name');
                //echo "name:{$name}\n";

                $reader->read();
                $value = null;
                if ($reader->name == 'field') {
                    // This field has no value
                    //echo "closing tag, value:{$reader->value}\n";
                    if (isset($numeric_stt[$name]) || match_numeric_dyt($name)) {
                        $value = 0;
                    } else {
                        //echo "continue on field, name:{$name}\n";
                        continue;
                    }
                } else {
                    $value = $reader->value;
                    $reader->read(); // read closing tag
                }
                //$value = $reader->value;
                //if ($value == "") {
                //    if (isset($numeric_stt[$name]) || match_numeric_dyt($name)) {
                //        $value = 0;
                //    }
                //}
                //if (isset($value) && $value != "") {
                //    if (!isset($doc[$name])) {
                //        $doc[$name] = array($value);
                //    } else {
                //        $doc[$name][] = $value;
                //    }
                //    //echo $name . ":" . $value . "\n";
                //} else {
                //    if (isset($numeric_stt[$name]) || match_numeric_dyt($name)) {
                //        $value = 0;
                //    }
                //}
                //if ($value === null) {
                //    continue;
                //}
                if (!isset($doc[$name])) {
                    // throw fields which in filter_fields array
                    if (!isset($filter_fields[$name])) {
                        $doc[$name] = array($value);
                    }
                } else {
                    $doc[$name][] = $value;
                }
                //$reader->read();  // Read field tail node
            }
        }
        //print_r($doc);
        if (isset($doc[$main_key][0])) {
            $docs[$doc[$main_key][0]] = $doc;
        }
    }
}
$reader->close();

// Regenerate xml string
$xml = '<add>';

foreach ($docs as $id => $product) {
    $xml.= '<doc>';
    foreach ($product as $product_attribute => $value) {
        if (is_array($value)) {
            foreach ($value as $v) {
                //$xml.= '<field name="' . $product_attribute . '">' . $v . '</field>';
                $xml.= '<field name="' . str_replace("<","&lt;",str_replace("&","&amp;",$product_attribute)) . '">' . str_replace("<","&lt;",str_replace("&","&amp;",$v)) . '</field>';
            }
        }else{
            $xml.= '<field name="' . str_replace("<","&lt;",str_replace("&","&amp;",$product_attribute)) . '">' . str_replace("<","&lt;",str_replace("&","&amp;",$v)) . '</field>';
            //$xml.= '<field name="' . $product_attribute . '">' . $v . '</field>';
        }
    }
    $xml.= '</doc>';
}

$xml.= '</add>';


// Write to new file
//$filepath .= ".1";
$fp = fopen($filepath, "w");
fwrite($fp, $xml);
fclose($fp);
exit(0);
?>

