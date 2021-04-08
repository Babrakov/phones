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

$opt  = array(
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'',
);
$dsn = 'mysql:host=localhost;dbname=phones;charset=UTF8';
$dbh = new PDO($dsn,'phones_user','WG3uTGA(ax3KkBHPZLu3', $opt);

$link = mysqli_connect('localhost','phones_user','WG3uTGA(ax3KkBHPZLu3','phones');
mysqli_set_charset($link, "utf8");
