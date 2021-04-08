<?php
require_once("../bootstrap.php");

//$sth = $dbh->query('SELECT * FROM phones WHERE id=2831');
//$row = $sth->fetch();
//echo $row['vc_fio'];

$link = mysqli_connect('localhost','phones_user','WG3uTGA(ax3KkBHPZLu3','phones');
mysqli_set_charset($link, "utf8");

//$query = 'SELECT id,vc_phone,vc_fio,vc_email FROM phones WHERE vc_email IS NOT NULL AND vc_email<>""';
//$query = 'SELECT id,vc_phone,vc_fio,vc_email,HEX(bn_hash) AS bn_hash FROM phones LIMIT 1000000 OFFSET 0';
//$query = 'SELECT id,vc_phone,vc_fio,vc_email,HEX(bn_hash) AS bn_hash FROM phones LIMIT 1000000 OFFSET 999999';
//$query = 'SELECT id,vc_phone,vc_fio,vc_email,HEX(bn_hash) AS bn_hash FROM phones LIMIT 1000000 OFFSET 1999999';
//$query = 'SELECT id,vc_phone,vc_fio,vc_email,HEX(bn_hash) AS bn_hash FROM phones LIMIT 1000000 OFFSET 2999999';
////$query = 'SELECT id,vc_phone,vc_fio,vc_email,HEX(bn_hash) AS bn_hash FROM phones WHERE id=1';
//$query = 'SELECT id,vc_phone,vc_fio,vc_email,HEX(bn_hash) AS bn_hash FROM phones LIMIT 1000000 OFFSET 3999999';
$query = 'SELECT id,vc_phone,vc_fio,vc_email,HEX(bn_hash) AS bn_hash FROM phones LIMIT 1000000 OFFSET 4999999';
$result = mysqli_query($link, $query);
//$sth = $dbh->query($query);
//while ($row = $sth->fetch()) {
while ($row = mysqli_fetch_assoc($result)) {
    $id = $row['id'];
//    $oldHash = hex2bin($row['bn_hash']);
    $oldHash = $row['bn_hash'];
//    echo ($oldHash); exit;
    $hash = md5($row['vc_phone'].$row['vc_fio'].$row['vc_email']);
    if ($oldHash !== mb_strtoupper($hash)) {
        $sql = "UPDATE `phones` SET bn_hash=UNHEX('$hash') WHERE id=$id";
        try {
            $dbh->query($sql);
        } catch (PDOExecption $e) {
            echo $e->getMessage();
        }
    }
}

require_once('../shutdown.php');
