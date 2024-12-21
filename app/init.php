<?php

// Support DEBUG cookie
error_reporting(0);
if ($_COOKIE['debug'] == 'true') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(-1);
}

require_once("vendor/autoload.php");
include_once("stdlib.php");

spl_autoload_register(function ($class_name) {
    include 'classes/' . $class_name . '.php';
});

date_default_timezone_set('America/New_York');

try {
    $pdo = new PDO(
        'mysql:host=liquidbooks_liquidbooks-db_1;dbname=liquid_books;charset=utf8mb4',
        'root',
        'root',
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING,
            PDO::ATTR_PERSISTENT => false
        )
    );
} catch (Exception $e) {
    exit($e);
}

$config = array(
    'dbo' => $pdo,
    'appName' => 'Liquid Books',
    'logLocal' => true
);
