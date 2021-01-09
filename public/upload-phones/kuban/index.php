<?php

$link = mysqli_connect('localhost','phones_user','WG3uTGA(ax3KkBHPZLu3','phones');

$source = 'КубаньСтройПодряд Недвижимость в Краснодаре.xlsx';
$date = '2020-09-25';
$file = 'file.csv';
$handle = fopen($file,'rt');

while (!feof($handle)) {
    $str = trim(fgets($handle)); // берем строку из файла
    $arr = explode("\t",$str);

    $name = $arr[0];
//    $phone = (int) filter_var($arr[1], FILTER_SANITIZE_NUMBER_INT);
//    $phone = filter_var($arr[1], FILTER_SANITIZE_NUMBER_INT);
//    echo validatePhone($phone).' :: '.$arr[1].'<br>';
    $phone = validatePhone(preg_replace('/[^0-9]/', '', $arr[1]));
    if ($phone) {
        $query = "INSERT INTO phones (vc_phone,vc_fio,dt_rec,tx_rem) VALUES ('$phone','$name','$date','$source')";
        $result = mysqli_query($link, $query);
//        echo $result;
    }
}

function validatePhone($pho)
{
    $len = strlen($pho);
    if ($len === 10) {
        return $pho;
    } else if ($len === 11) {
        if ( (substr($pho,0,1) === '7') || (substr($pho,0,1) === '8') ) {
            return substr($pho,1);
        }
    }
    return false;
}
