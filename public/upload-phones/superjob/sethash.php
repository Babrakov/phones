<?php
$link = mysqli_connect('localhost','phones_user','WG3uTGA(ax3KkBHPZLu3','phones');
$query = 'SELECT * FROM phones WHERE bn_hash IS NULL';
$result = mysqli_query($link, $query);
while ($row = mysqli_fetch_assoc($result)) {
//    echo $row[0];
//    echo '<br>';
    $id = $row['id'];
    $hash = md5($row['vc_phone']
        .$row['vc_fio']
        .$row['dt_born']
        .$row['sex_id']
        .$row['vc_region']
        .$row['vc_city']
        .$row['tx_location']
        .$row['vc_email']
        .$row['vc_link']
    );
//    echo $hash;
//    echo '<br>';
    mysqli_query($link, "UPDATE phones SET bn_hash=UNHEX('$hash') WHERE id=$id");
}
exit;

$file = 'hash.csv';
$handle = fopen($file,'rt');
//$str = fgets($handle); // пропускаем 1-ю строку
$str = trim(fgets($handle)); // берем строку из файла
$arr = explode("\t",$str);
$name = $arr[1];
$phone = validatePhone(preg_replace('/[^0-9]/', '', $arr[5]));
$sex = $arr[2];
$born = getDtBorn($arr[3]);
$email = $arr[9];
$city = $arr[10];
$region = $arr[7];
if ($arr[4]) {
    $rem = 'Желаемая зарплата: '.$arr[4];
} else {
    $rem = '';
}
$location = $arr[8];
$link = 'https://infoza.ru';

echo $phone.$name.$born.$sex.$region.$city.$location.$email.$link.'<br>';

$md5 = md5($phone.$name.$born.$sex.$region.$city.$location.$email.$link);
echo $md5.'<br>';


$link = mysqli_connect('localhost','phones_user','WG3uTGA(ax3KkBHPZLu3','phones');

$query = "SELECT count(1) FROM phones WHERE bn_hash=UNHEX('$md5')";
$result = mysqli_query($link, $query);
$row = mysqli_fetch_row($result);
echo $row[0];

//echo md5('Бабраков Сергей Анатольевич');

exit;

$link = mysqli_connect('localhost','phones_user','WG3uTGA(ax3KkBHPZLu3','phones');

$source = 'SJ_Ч5_900к _РФ (Без МСК и СПБ)_(Vers 2.0).xlsx';
$date = '2017-05-14';
$file = 'file.csv';
$handle = fopen($file,'rt');
$str = fgets($handle); // пропускаем 1-ю строку
while (!feof($handle)) {
    $str = trim(fgets($handle)); // берем строку из файла
    $arr = explode("\t",$str);

    $name = $arr[1];
//    $phone = (int) filter_var($arr[1], FILTER_SANITIZE_NUMBER_INT);
//    $phone = filter_var($arr[1], FILTER_SANITIZE_NUMBER_INT);
//    echo validatePhone($phone).' :: '.$arr[1].'<br>';
    $phone = validatePhone(preg_replace('/[^0-9]/', '', $arr[5]));
    $sex = getSex($arr[2]);
    $born = getDtBorn($arr[3]);
    $email = $arr[9];
    $city = $arr[10];
    $region = $arr[7];
    if ($arr[4]) {
        $rem = 'Желаемая зарплата: '.$arr[4];
    } else {
        $rem = '';
    }
    $location = '';
    $link = '';

//    $ifNotExist = checkIfExist($phone.$name.$born.$sex.$region.$city.$location.$email.$link.$rem);
    $ifNotExist = checkIfExist($phone.$name.$born.$sex.$region.$city.$location.$email.$link);

    if ($phone && $ifNotExist) {
        $query = "INSERT INTO phones
            (vc_phone,vc_fio,dt_rec,vc_source,sex_id,dt_born,vc_email,vc_city,vc_region,tx_rem)
            VALUES
            ('$phone','$name','$date','$source',$sex,'$born','$email','$city','$region','$rem')";
//echo $query;
        $result = mysqli_query($link, $query);
//        echo $result;
    }
}

function checkIfExist($str,$link)
{
    $hash = md5($str);
    $query = "SELECT count(1) FROM phones WHERE bn_hash=UNHEX('$hash'))";
    $result = mysqli_query($link, $query);
    $row = mysqli_fetch_row($result);
    if ($row[0] > 0) {
        return false;
    } else {
        return true;
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

function getSex($str)
{
    if ($str === 'М') {
        return 1;
    } else if ($str === 'Ж') {
        return 2;
    } else {
        return 'null';
    }
}

function getDtBorn($str)
{
    if ($str) {
        return date('Y-m-d', strtotime($str));
    } else {
        return 'null';
    }
}
