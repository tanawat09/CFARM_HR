<?php
$pdo = new PDO('mysql:host=192.168.7.3;port=3308', 'itadmin', 'Noipalee@09');
echo 'Connected! ';
$pdo->exec('CREATE DATABASE IF NOT EXISTS cfarm_hr');
echo 'Database exists/created. ';
?>
