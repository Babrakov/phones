<?php
ini_set('max_execution_time', 0);

    //Считываем текущее время
    $mtime = microtime();
    //Разделяем секунды и миллисекунды
    $mtime = explode(" ",$mtime);
    //Составляем одно число из секунд и миллисекунд
    $mtime = $mtime[1] + $mtime[0];
    //Записываем стартовое время в переменную
    $startTime = $mtime;

//$file = 'hash.csv';
//$handle = fopen($file,'rt');
////$str = fgets($handle); // пропускаем 1-ю строку
//$str = trim(fgets($handle)); // берем строку из файла
//$arr = explode("\t",$str);
//$name = $arr[1];
//$phone = validatePhone(preg_replace('/[^0-9]/', '', $arr[5]));
//$sex = $arr[2];
//$born = getDtBorn($arr[3]);
//$email = $arr[9];
//$city = $arr[10];
//$region = $arr[7];
//if ($arr[4]) {
//    $rem = 'Желаемая зарплата: '.$arr[4];
//} else {
//    $rem = '';
//}
//$location = $arr[8];
//$url = 'https://infoza.ru';
//
//echo $phone.$name.$born.$sex.$region.$city.$location.$email.$url.'<br>';
//
//$md5 = md5($phone.$name.$born.$sex.$region.$city.$location.$email.$url);
//echo $md5.'<br>';


//$link = mysqli_connect('localhost','phones_user','WG3uTGA(ax3KkBHPZLu3','phones');
//
//$query = "SELECT count(1) FROM phones WHERE bn_hash=UNHEX('$md5')";
//$result = mysqli_query($link, $query);
//$row = mysqli_fetch_row($result);
//echo $row[0];
//
////echo md5('Бабраков Сергей Анатольевич');
//
//exit;

$link = mysqli_connect('localhost','phones_user','WG3uTGA(ax3KkBHPZLu3','phones');

$date = '2017-05-14';

//$file = 'file0.csv';
//$source = 'SJ_Ч4_900к _РФ (Без МСК и СПБ)_(Vers 2.0).xlsx';

//$file = 'file1.csv';
//$source = 'SJ_Ч5_900к _РФ (Без МСК и СПБ)_(Vers 2.0).xlsx';

//$file = 'file2.csv';
//$source = 'SJ_Ч6_880к _РФ (Без МСК и СПБ)_(Vers 2.0).xlsx';

$file = 'file.csv';
$source = '5';

$handle = fopen($file,'rt');
$str = fgets($handle); // пропускаем 1-ю строку
$counter = 0; // счетчик для буфера запросов
$i = 0;
while (!feof($handle)) {
    $i++;
//    if ( $i % 1000 === 0 ) {
//        echo $i.'<br>';
//    }
    $str = trim(fgets($handle)); // берем строку из файла
    $str = str_replace("'",'',$str);
    $arr = explode("\t",$str);
    if (count($arr) < 10) {
        continue;
    }

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
    $url = '';

    if ($sex === 'null') {
        $sex4hash = '';
    } else {
        $sex4hash = $sex;
    }
    if ($born === 'null') {
        $born4hash = '';
    } else {
        $born4hash = $born;
    }
    $hash = md5($phone.$name.$born4hash.$sex4hash.$region.$city.$location.$email.$url);
//    $ifNotExist = checkIfExist($phone.$name.$born.$sex.$region.$city.$location.$email.$link.$rem);
    $ifNotExist = checkIfNotExist($hash,$link,$i);
//continue;
    if ($phone && $ifNotExist) {
        if ($counter === 0) {
            $query = "INSERT INTO phones
                (vc_phone,vc_fio,dt_rec,source_id,sex_id,dt_born,vc_email,vc_city,vc_region,tx_rem,bn_hash)
                VALUES
                ('$phone','$name','$date','$source',$sex,'$born','$email','$city','$region','$rem',UNHEX('$hash'))";
        } else {
            $query .= ",('$phone','$name','$date','$source',$sex,'$born','$email','$city','$region','$rem',UNHEX('$hash'))";
        }
        $counter++;
        if ($counter === 300) {
//            echo $query;
            $result = mysqli_query($link, $query);
//            echo $result;
            $counter = 0;
        }
    }
}
if ($counter) {
    $result = mysqli_query($link, $query);
    $counter = 0;
}

// Делаем все то же самое, чтобы получить текущее время
$mtime = microtime();
$mtime = explode(" ",$mtime);
$mtime = $mtime[1] + $mtime[0];
//Записываем время окончания в другую переменную
$tend = $mtime;
//Вычисляем разницу
$totaltime = ($tend - $startTime);
//Выводим не экран
//$exectime = sprintf ("Страница сгенерирована за %f секунд !", $totaltime);
$exectime = sprintf ("Script total run-time %f second(s)!", $totaltime);
echo $exectime;

function checkIfNotExist($hash,$link,$i)
{
    $query = "SELECT count(1),$i FROM phones WHERE bn_hash=UNHEX('$hash')";
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
