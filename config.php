<?php
$databaseConfig = [
    'host' => '127.0.0.1',
    'port' => 3306,
    'database' => 'nexa',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
];

$dsn = sprintf(
    'mysql:host=%s;port=%d;dbname=%s;charset=%s',
    $databaseConfig['host'],
    $databaseConfig['port'],
    $databaseConfig['database'],
    $databaseConfig['charset']
);

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $databaseConfig['username'], $databaseConfig['password'], $options);
} catch (PDOException $exception) {
    exit('Database connection failed: ' . $exception->getMessage());
}
