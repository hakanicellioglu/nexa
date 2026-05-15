<?php
/**
 * config.php
 *
 * Genel uygulama yapılandırması ve veritabanı bağlantısı.
 * dashboard.php dosyası bu dosyayı yükleyerek $pdo PDO nesnesini alır.
 */

$databaseConfig = [
    'host'    => '127.0.0.1',
    'name'    => 'dashboard',
    'user'    => 'root',
    'pass'    => '',
    'charset' => 'utf8mb4',
];

$dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s',
    $databaseConfig['host'],
    $databaseConfig['name'],
    $databaseConfig['charset']
);

$pdoOptions = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $databaseConfig['user'], $databaseConfig['pass'], $pdoOptions);
} catch (PDOException $e) {
    throw new RuntimeException('Veritabanı bağlantısı kurulamadı: ' . $e->getMessage());
}

return $pdo;
