<?php
require_once('../bootstrap.php');

$table = 'phones_copy';

$link = mysqli_connect('localhost','phones_user','WG3uTGA(ax3KkBHPZLu3','phones');
mysqli_set_charset($link, "utf8");

//$sth = $dbh->query('SELECT id,vc_phone,vc_fio,vc_email FROM phones WHERE vc_email IS NOT NULL AND vc_email<>"" LIMIT 1000000 OFFSET 0');
////$sth = $dbh->query('SELECT * FROM phones WHERE id=2831');
//while ($row = $sth->fetch()) {
//    $id = $row['id'];
//    $hash = md5($row['vc_phone'].$row['vc_fio'].$row['vc_email']);
//    $sql = "UPDATE `phones` SET bn_hash=UNHEX('$hash') WHERE id=$id";
//    try {
//        $sth = $dbh->query($sql);
//    } catch(PDOExecption $e) {
//        echo $e->getMessage();
//    }
//}
//
////$row = $sth->fetch();
////$pho = $row['vc_phone'];
////$name = $row['vc_fio'];
////$email = $row['vc_email'];
////
////echo md5($pho.$name)."<br>";
////echo md5($pho.$name.$email);
//
//exit;

//$placeholder = substr(str_repeat('?,',count($keys)),0,-1);
//
//// vcREM не должен быть NULL
//$sth = $dbh->prepare("INSERT INTO `fls`"
//    . " ($fields,`txRem`,`created_at`) VALUES "
//    . " ($placeholder,'',now())");
//
//try {
//    if (DEBUG) {
//        $id = -1;
//    } else {
//        $sth->execute(array_values($data));
//        $id = $dbh->lastInsertId();
//    }
////            $log->toLog('Добавлен новый физик: '.$this->toString());
//} catch(PDOExecption $e) {
//    $log->toLog($e->getMessage());
//}

$undoFile = 'undo.sql';
$undoHandle = fopen($undoFile,'w+');
$badFile = 'bad.txt';
$badHandle = fopen($badFile,'w+');
$query = "SELECT max(id) FROM `$table`";
$result = mysqli_query($link, $query);
$row = mysqli_fetch_row($result);
$max = $row[0]+1;
fwrite($undoHandle,"ALTER TABLE `$table` AUTO_INCREMENT=$max;\n");

$source = '10';
$file = 'file.csv';
//$file = 'test.csv';
$handle = fopen($file,'rt');

$str = fgets($handle); // пропускаем 1-ю строку
$counter = 0; // счетчик для буфера запросов
$i = 0;
while (!feof($handle)) {
    $i++;
    $str = trim(fgets($handle)); // берем строку из файла
    $str = str_replace("'",'',$str);
    $arr = explode("\t",$str);
//    $arr = explode(";",$str);
    if (count($arr) < 4) {
        fwrite($badHandle,"Не хватает полей в записи:\n");
        fwrite($badHandle,$str."\n");
        continue;
    }

    $name = $arr[0];
    $adr = explode(',',$arr[3]);
    $phone = validatePhone(preg_replace('/[^0-9]/', '', $arr[1]));
//    $phone = validatePhone(preg_replace('/[^0-9]/', '', $arr[2]));
    $email = null;
//    $born = getDtBorn($arr[3]);
//    $city = getTownId($arr[4],$link);
    $city = '1791';
    $region = '92';

    if ($phone) {
//        if (!empty(trim($adr[2]))) {
//            fwrite($badHandle,"В адресе заполнено 2 поле (город?):\n");
//            fwrite($badHandle,$str."\n");
//            continue;
//        }
//        if (!empty(trim($adr[7]))) {
//            fwrite($badHandle,"В адресе заполнено 7 поле (квартира?):\n");
//            fwrite($badHandle,$str."\n");
//            continue;
//        }

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
                (vc_phone,vc_fio,source_id,region_id,town_id,tx_location,bn_hash,created_at)
                VALUES
                ('$phone','$name','$source','$region','$city','$location',UNHEX('$hash'),now())";
            } else {
                $query .= ",('$phone','$name','$source','$region','$city','$location',UNHEX('$hash'),now())";
            }
            $counter++;
            if ($counter === 300) {
                $result = mysqli_query($link, $query);
                $first = mysqli_insert_id($link);
                $last = $first + mysqli_affected_rows($link) - 1;
                fwrite($undoHandle,"DELETE FROM phones WHERE id BETWEEN $first AND $last;\n");
                $counter = 0;
            }

        }

    }
//    else {
//        fwrite($badHandle,"Некорректный телефон:\n");
//        fwrite($badHandle,$str."\n");
//        continue;
//    }
}
if ($counter) {
//    echo $query;
    $result = mysqli_query($link, $query);
    $first = mysqli_insert_id($link);
    $last = $first + mysqli_affected_rows($link) - 1;
    fwrite($undoHandle,"DELETE FROM `$table` WHERE id BETWEEN $first AND $last;\n");
    $counter = 0;
}

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
