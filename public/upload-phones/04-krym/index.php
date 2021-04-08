<?php
require_once('../bootstrap.php');

$table = 'phones';
$undoFile = 'undo.sql';
$undoHandle = fopen($undoFile,'w+');
$badFile = 'bad.txt';
$badHandle = fopen($badFile,'w+');
$query = "SELECT max(id) FROM `$table`";
$result = mysqli_query($link, $query);
$row = mysqli_fetch_row($result);
$max = $row[0]+1;

$source = '10';
$file = 'file.csv';
//$file = 'test.csv';
$handle = fopen($file,'rt');

$str = fgets($handle); // пропускаем 1-ю строку
$counter = 0; // счетчик для буфера запросов
$i = 0;

$city = '1791';
$region = '92';

while (!feof($handle)) {
    $i++;
    $str = trim(fgets($handle)); // берем строку из файла
    $str = str_replace("'",'',$str);
    $arr = explode("\t",$str);
//    $arr = explode(";",$str);
    if (count($arr) < 4) {
        fwrite($badHandle,"Не хватает полей в записи\t".$str."\n");
        continue;
    }

    $name = $arr[0];
    $sex = getSex($name);
    $adr = explode(',',$arr[3]);
    $phone = validatePhone(preg_replace('/[^0-9]/', '', $arr[2]));
    $email = validateEmail($arr[1]);
    if ($arr[1] && !$email) {
        fwrite($badHandle,"В поле email ошибка\t".$str."\n");
        continue;
    }

    if ($phone) {
        $smallAdr = array_slice($adr, 2);
        foreach ($smallAdr as $key=>$value) {
            if (empty(trim($value))) {
                unset($smallAdr[$key]);
            }
        }
        $location = trim(implode(' ',$smallAdr));

        $hash = md5($phone.$name.$email);
        $ifNotExist = checkIfNotExist($hash,$dbh,$i,$table);
        if ($ifNotExist) {
            if ($counter === 0) {
                $query = "INSERT INTO `$table`
                (vc_phone,vc_fio,sex_id,source_id,region_id,town_id,tx_location,bn_hash,created_at)
                VALUES
                ('$phone','$name',$sex,'$source','$region','$city','$location',UNHEX('$hash'),now())";
            } else {
                $query .= ",('$phone','$name',$sex,'$source','$region','$city','$location',UNHEX('$hash'),now())";
            }
            $counter++;
            if ($counter === 300) {
                $result = mysqli_query($link, $query);
                $first = mysqli_insert_id($link);
                $last = $first + mysqli_affected_rows($link) - 1;
                fwrite($undoHandle,"DELETE FROM `phones` WHERE id BETWEEN $first AND $last;\n");
                $counter = 0;
            }

        }

    }
    else {
        fwrite($badHandle,"Некорректный телефон\t".$str."\n");
        continue;
    }
}
if ($counter) {
//    echo $query;
    $result = mysqli_query($link, $query);
    $first = mysqli_insert_id($link);
    $last = $first + mysqli_affected_rows($link) - 1;
    fwrite($undoHandle,"DELETE FROM `$table` WHERE id BETWEEN $first AND $last;\n");
    $counter = 0;
}

fwrite($undoHandle,"ALTER TABLE `$table` AUTO_INCREMENT=$max;\n");

fclose($badHandle);
fclose($undoHandle);
fclose($handle);

require_once('../shutdown.php');

function checkIfNotExist($hash,$dbh,$i,$table)
{
//    $query = "SELECT count(1),$i FROM phones WHERE bn_hash=UNHEX('$hash')";
//    $result = mysqli_query($link, $query);
//    $row = mysqli_fetch_row($result);
//    if ($row[0] > 0) {
//        return false;
//    } else {
//        return true;
//    }
    $sth = $dbh->query("SELECT count(1),$i FROM `$table` WHERE bn_hash=UNHEX('$hash')");
    $row = $sth->fetch(PDO::FETCH_NUM);
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


function getDtBorn($str)
{
    if ($str) {
        return date('Y-m-d', strtotime($str));
    } else {
        return 'null';
    }
}

function validateEmail($email)
{
    $re = '/^\S+@\S+\.\S+$/';
    if (preg_match($re, $email)) {
        return $email;
    } else {
        return false;
    }

}

function getSex($name)
{
    if (mb_strtoupper(mb_substr($name,-2,2)) == 'ИЧ') {
        $sex = '1';
    } elseif (mb_strtoupper(mb_substr($name,-2,2)) == 'НА') {
        $sex = '2';
    } else {
        $sex = 'NULL';
    }
    return $sex;
}
