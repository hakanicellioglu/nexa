<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Yalnızca POST metoduna izin verilmektedir.',
    ]);
    exit;
}

if (! isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Bu işlem için yetkiniz yok.',
    ]);
    exit;
}

require_once __DIR__ . '/../../config.php';

$rawInput = file_get_contents('php://input');
$payload = json_decode($rawInput, true);

if (! is_array($payload)) {
    $payload = $_POST;
}

$name = isset($payload['name']) ? trim($payload['name']) : '';

if ($name === '') {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Tedarikçi adı gerekli.',
        'errors' => ['name' => 'Tedarikçi adı boş olamaz.'],
    ]);
    exit;
}

$stmt = $mysqli->prepare('INSERT INTO suppliers (name) VALUES (?)');

if (! $stmt) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Veritabanı hatası: ' . $mysqli->error,
    ]);
    exit;
}

$stmt->bind_param('s', $name);

if (! $stmt->execute()) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Tedarikçi eklenemedi. Lütfen tekrar deneyin.',
    ]);
    $stmt->close();
    exit;
}

$newSupplierId = $stmt->insert_id;
$stmt->close();

$selectStmt = $mysqli->prepare('SELECT id, name, created_at, updated_at FROM suppliers WHERE id = ?');

if (! $selectStmt) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Veritabanı hatası: ' . $mysqli->error,
    ]);
    exit;
}

$selectStmt->bind_param('i', $newSupplierId);
$selectStmt->execute();
$result = $selectStmt->get_result();
$supplierData = $result ? $result->fetch_assoc() : null;
$selectStmt->close();

if (! $supplierData) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Tedarikçi bilgileri alınamadı.',
    ]);
    exit;
}

http_response_code(201);
echo json_encode([
    'success' => true,
    'message' => 'Tedarikçi başarıyla eklendi.',
    'supplier' => $supplierData,
]);
