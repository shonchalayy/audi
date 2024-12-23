<?php

$DBuser = 'root';
$DBpass = $_ENV['MYSQL_ROOT_PASSWORD'];
$database = 'mysql:host=database:3306; dbname=docker';
return new PDO($database, $DBuser, $DBpass);
