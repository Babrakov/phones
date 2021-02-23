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

$link = mysqli_connect('localhost','phones_user','WG3uTGA(ax3KkBHPZLu3','phones');
mysqli_set_charset($link, "utf8");

$undoFile = 'undo.sql';
$undoHandle = fopen($undoFile,'w+');
$query = "SELECT max(id) FROM phones";
$result = mysqli_query($link, $query);
$row = mysqli_fetch_row($result);
$max = $row[0]+1;
fwrite($undoHandle,"ALTER TABLE phones AUTO_INCREMENT=$max;\n");

$source = '2';
$file = 'file.csv';
$handle = fopen($file,'rt');

$str = fgets($handle); // пропускаем 1-ю строку
$counter = 0; // счетчик для буфера запросов
$i = 0;
while (!feof($handle)) {
    $i++;
    $str = trim(fgets($handle)); // берем строку из файла
    $str = str_replace("'",'',$str);
    $arr = explode("\t",$str);
    if (count($arr) < 2) {
        continue;
    }

    $name = $arr[0];
    $phone = validatePhone(preg_replace('/[^0-9]/', '', $arr[1]));

    $hash = md5($phone.$name);
    $ifNotExist = checkIfNotExist($hash,$link,$i);

    if ($phone && $ifNotExist) {
        if ($counter === 0) {
            $query = "INSERT INTO phones
                (vc_phone,vc_fio,source_id,bn_hash)
                VALUES
                ('$phone','$name','$source',UNHEX('$hash'))";
        } else {
            $query .= ",('$phone','$name','$source',UNHEX('$hash'))";
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
    $result = mysqli_query($link, $query);
    $first = mysqli_insert_id($link);
    $last = $first + mysqli_affected_rows($link) - 1;
    fwrite($undoHandle,"DELETE FROM phones WHERE id BETWEEN $first AND $last;\n");
    $counter = 0;
}

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
