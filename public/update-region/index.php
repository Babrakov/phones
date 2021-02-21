<?php

ini_set('max_execution_time', 0);

$link = mysqli_connect('localhost', 'phones_user', 'WG3uTGA(ax3KkBHPZLu3', 'phones');
mysqli_set_charset($link, "utf8");

$query = "SELECT id, vc_region FROM phones WHERE region_id IS NULL AND vc_region IS NOT NULL";
//$query = "SELECT id, vc_region FROM phones WHERE region_id IS NULL AND vc_region IS NOT NULL LIMIT 1000";
$result = mysqli_query($link,$query);

while ($row = mysqli_fetch_assoc($result)) {
    if ($row['vc_region']) {

        $id = $row['id'];
        $region = $row['vc_region'];
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
        $region = str_replace(' - ','-',$region);
        $region = trim(preg_replace('/(край|область|обл\.|АО|-Югра|-Алания|-Чувашия)/','',$region));
        $sql = 'SELECT id,vc_name FROM regions WHERE vc_name LIKE "'.$region.'%" OR vc_sname LIKE "'.$region.'%"';
        $res = mysqli_query($link,$sql);
        $num = mysqli_num_rows($res);
        if ($num) {
            $r = mysqli_fetch_assoc($res);
            $region_id = $r['id'];
            mysqli_query($link,"UPDATE phones SET region_id=$region_id WHERE id=$id");
//            echo ' :: '.$r['vc_name'];
        } else{

            echo $row['vc_region'];
            echo '<font color="red"> :: не найдено</font>';
            echo "<br>$id: <b>".$row['vc_region'].'</b><br>';
//            echo "<br>$sql<br>";

        }

    }
}
