<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config.php';

$allowedMethods = ['POST', 'DELETE'];

if (!in_array($_SERVER['REQUEST_METHOD'], $allowedMethods, true)) {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'İstek yöntemi desteklenmiyor. POST veya DELETE kullanın.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$rawInput = file_get_contents('php://input');
$data = [];

if (!empty($rawInput)) {
    $decoded = json_decode($rawInput, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $data = $decoded;
    }
}

if (empty($data)) {
    $data = $_POST;
}

$id = isset($data['id']) ? (int) $data['id'] : 0;

if ($id <= 0) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Geçerli bir ürün kimliği belirtilmelidir.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$tableCheck = $connection->query("SHOW TABLES LIKE 'products'");
if ($tableCheck === false || $tableCheck->num_rows === 0) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Products tablosu bulunamadı. Lütfen veritabanını güncelleyin.'
    ], JSON_UNESCAPED_UNICODE);
    if ($tableCheck instanceof mysqli_result) {
        $tableCheck->free();
    }
    exit;
}

if ($tableCheck instanceof mysqli_result) {
    $tableCheck->free();
}

$stmt = $connection->prepare('DELETE FROM products WHERE id = ?');

if ($stmt === false) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Veritabanı hatası: ' . $connection->error
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt->bind_param('i', $id);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Ürün silinemedi: ' . $stmt->error
    ], JSON_UNESCAPED_UNICODE);
    $stmt->close();
    exit;
}

if ($stmt->affected_rows === 0) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'Ürün bulunamadı.'
    ], JSON_UNESCAPED_UNICODE);
    $stmt->close();
    exit;
}

$stmt->close();

echo json_encode([
    'success' => true,
    'message' => 'Ürün başarıyla silindi.',
    'data' => [
        'id' => $id
    ]
], JSON_UNESCAPED_UNICODE);
