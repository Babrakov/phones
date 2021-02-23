<?php
//$undoFile = 'undo.sql';
//$undoHandle = fopen($undoFile,'w+');
//fwrite($undoHandle,'swssss');
//fclose($undoHandle);
//echo 'test'.PHP_EOL;exit;
ini_set('max_execution_time', 0);

//Считываем текущее время
$mtime = microtime();
//Разделяем секунды и миллисекунды
$mtime = explode(" ",$mtime);
//Составляем одно число из секунд и миллисекунд
$mtime = $mtime[1] + $mtime[0];
//Записываем стартовое время в переменную
$startTime = $mtime;

$link = mysqli_connect('localhost','phones_user','WG3uTGA(ax3KkBHPZLu3','phones');
mysqli_set_charset($link, "utf8");

$undoFile = 'undo.sql';
$undoHandle = fopen($undoFile,'w+');
$badFile = 'bad.txt';
$badHandle = fopen($badFile,'w+');
$query = "SELECT max(id) FROM phones";
$result = mysqli_query($link, $query);
$row = mysqli_fetch_row($result);
$max = $row[0]+1;
fwrite($undoHandle,"ALTER TABLE phones AUTO_INCREMENT=$max;\n");

$source = '8';
$file = 'file.csv';
//$file = 'test.csv';
$handle = fopen($file,'rt');

//$str = fgets($handle); // пропускаем 1-ю строку
$counter = 0; // счетчик для буфера запросов
$i = 0;
while (!feof($handle)) {
    $i++;
    $str = trim(fgets($handle)); // берем строку из файла
//    echo $str."\n";
    $str = str_replace("'",'',$str);
    $arr = explode("\t",$str);
//    $arr = explode(",",$str);
//    $arr = explode(";",$str);
    if (count($arr) < 10) {
        continue;
    }

    $name = $arr[1];
    $phone = validatePhone(preg_replace('/[^0-9]/', '', $arr[5]));
    $sex = getSex($arr[2]);
    $born = getDtBorn($arr[3]);
    $email = $arr[9];
//if ($i<31) continue;
//if ($i>31) break;
    $town = explode('(',$arr[10]);
    if (count($town)>1) {
//        print_r($town);
        $city = getTownId($town[0],$link);
        $region = $town[1];
        $region = getRegionId($region,$link);
    } else {
        $city = getTownId($arr[10], $link);
        $region = $arr[7];
        $region = getRegionId($region, $link);
    }

    if ($arr[4]) {
        $rem = 'Желаемая зарплата: '.$arr[4];
    } else {
        $rem = '';
    }
    $location = '';
    $url = '';

    $hash = md5($phone.$name);
    $ifNotExist = checkIfNotExist($hash,$link,$i);

//continue;
    if (!$phone || (!$city && $arr[10]) || (!$region && $arr[7])) {
        fwrite($badHandle,$str."\n");
    }else if ($ifNotExist) {
        if ($counter === 0) {
            $query = "INSERT INTO phones
                (vc_phone,vc_fio,source_id,sex_id,dt_born,vc_email,town_id,region_id,tx_rem,bn_hash)
                VALUES
                ('$phone','$name','$source',$sex,'$born','$email','$city','$region','$rem',UNHEX('$hash'))";
        } else {
            $query .= ",('$phone','$name','$source',$sex,'$born','$email','$city','$region','$rem',UNHEX('$hash'))";
        }
        $counter++;
        if ($counter === 300) {
//            echo $query;
            $result = mysqli_query($link, $query);
            $first = mysqli_insert_id($link);
            $last = $first + mysqli_affected_rows($link) - 1;
            fwrite($undoHandle,"DELETE FROM phones WHERE id BETWEEN $first AND $last;\n");
            $counter = 0;
        }
    }
}
if ($counter) {
//    echo $query;
    $result = mysqli_query($link, $query);
    $first = mysqli_insert_id($link);
    $last = $first + mysqli_affected_rows($link) - 1;
    fwrite($undoHandle,"DELETE FROM phones WHERE id BETWEEN $first AND $last;\n");
    $counter = 0;
}

fclose($badHandle);
fclose($undoHandle);
fclose($handle);

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
$exectime = sprintf ("Script total run-time %f second(s)!".PHP_EOL, $totaltime);
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

function getTownId($str,$link)
{
    $query = "SELECT id FROM towns WHERE vc_name='$str'";
    $result = mysqli_query($link, $query);
    $num = mysqli_num_rows($result);
    if ($num > 0) {
        $row = mysqli_fetch_row($result);
        return $row[0];
    } else {
        return null;
    }
}

function getRegionId($region,$link)
{

    $region = str_replace('Ханты - Мансийский - Югра АО','Ханты-Мансийский',$region);
    $region = str_replace('г. Санкт - Петербург и Ленинградская обл.','Ленинградская',$region);
    $region = str_replace('г. Тюмень  |  Тюменская обл.','Тюменская',$region);
    $region = str_replace('г. Севастополь и Республика Крым','Республика Крым',$region);
    $region = str_replace('г. Норильск  |  Красноярский край','Красноярский',$region);
    $region = str_replace('г. Сочи  |  Краснодарский край','Краснодарский',$region);
    $region = str_replace('Архангельская обл. и Ненецкий АО','Архангельская',$region);
    $region = str_replace('г. Новокузнецк  |  Кемеровская обл.','Кемеровская',$region);
    $region = str_replace('Республика Саха /Якутия/','Республика Саха',$region);
    $region = str_replace('Республика Удмуртская','Удмуртская',$region);
    $region = str_replace('Республика Кабардино - Балкарская','Кабардино-Балкарская',$region);
    $region = str_replace('Республика Карачаево-Черкесская','Карачаево-Черкесская',$region);
    $region = str_replace('Республика Карачаево - Черкесская','Карачаево-Черкесская',$region);
    $region = str_replace('Республика Чеченская','Чеченская',$region);
    $region = trim(preg_replace('/(край|область|обл\.|\)|АО)/','',$region));
    $region = str_replace(' - ','-',$region);

    $query = "SELECT id FROM regions WHERE vc_name LIKE '$region%' OR vc_sname LIKE '$region%'";
    $result = mysqli_query($link, $query);
    $num = mysqli_num_rows($result);
//    echo $query;
    if ($num > 0) {
        $row = mysqli_fetch_row($result);
        return $row[0];
    } else {
        return null;
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
