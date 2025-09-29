<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'nexa';

$connection = new mysqli($host, $username, $password, $database);

if ($connection->connect_error) {
    die('Veritabanı bağlantısı başarısız: ' . $connection->connect_error);
}
?>