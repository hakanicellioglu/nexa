<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config.php';

$allowedMethods = ['POST', 'PUT', 'PATCH'];
if (! in_array($_SERVER['REQUEST_METHOD'], $allowedMethods, true)) {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Yalnızca POST, PUT veya PATCH isteklerine izin verilmektedir.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);
if (! is_array($data)) {
    $data = $_POST;
}

$id = isset($data['id']) ? (int) $data['id'] : 0;
$name = isset($data['name']) ? trim($data['name']) : '';
$type = isset($data['type']) ? trim($data['type']) : '';
$allowedTypes = ['Isıcam', 'Tekcam'];

if ($id <= 0) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Geçerli bir ürün kimliği belirtilmelidir.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($name === '') {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Ürün adı boş bırakılamaz.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($type !== '' && ! in_array($type, $allowedTypes, true)) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Geçerli bir cam türü seçiniz.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$existsStmt = $mysqli->prepare('SELECT id FROM products WHERE id = ?');
if (! $existsStmt) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Ürün doğrulanırken bir hata oluştu.',
        'details' => $mysqli->error,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$existsStmt->bind_param('i', $id);
$existsStmt->execute();
$existsResult = $existsStmt->get_result();
if ($existsResult->num_rows === 0) {
    $existsStmt->close();
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'Ürün bulunamadı.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
$existsStmt->close();

$updateStmt = $mysqli->prepare('UPDATE products SET name = ?, type = ? WHERE id = ?');
if (! $updateStmt) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Ürün güncellenirken bir hata oluştu.',
        'details' => $mysqli->error,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$typeParam = $type !== '' ? $type : null;
$updateStmt->bind_param('ssi', $name, $typeParam, $id);

if (! $updateStmt->execute()) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Ürün güncellenirken bir hata oluştu.',
        'details' => $updateStmt->error,
    ], JSON_UNESCAPED_UNICODE);
    $updateStmt->close();
    exit;
}
$updateStmt->close();

$productStmt = $mysqli->prepare('SELECT id, name, type, created_at, updated_at FROM products WHERE id = ?');
if (! $productStmt) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Güncellenen ürün alınamadı.',
        'details' => $mysqli->error,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$productStmt->bind_param('i', $id);
$productStmt->execute();
$result = $productStmt->get_result();
$product = $result->fetch_assoc();
$productStmt->close();

http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'Ürün başarıyla güncellendi.',
    'data' => $product,
], JSON_UNESCAPED_UNICODE);
