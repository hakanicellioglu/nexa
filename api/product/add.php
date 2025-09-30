<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Yalnızca POST isteklerine izin verilmektedir.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);
if (! is_array($data)) {
    $data = $_POST;
}

$name = isset($data['name']) ? trim($data['name']) : '';
$type = isset($data['type']) ? trim($data['type']) : '';

if ($name === '') {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Lütfen ürün adını belirtin.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt = $mysqli->prepare('INSERT INTO products (name, type) VALUES (?, ?)');
if (! $stmt) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Ürün eklenirken bir hata oluştu.',
        'details' => $mysqli->error,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$typeParam = $type !== '' ? $type : null;
$stmt->bind_param('ss', $name, $typeParam);

if (! $stmt->execute()) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Ürün eklenirken bir hata oluştu.',
        'details' => $stmt->error,
    ], JSON_UNESCAPED_UNICODE);
    $stmt->close();
    exit;
}

$newProductId = $stmt->insert_id;
$stmt->close();

$productStmt = $mysqli->prepare('SELECT id, name, type, created_at, updated_at FROM products WHERE id = ?');
if (! $productStmt) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Yeni eklenen ürün alınamadı.',
        'details' => $mysqli->error,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$productStmt->bind_param('i', $newProductId);
$productStmt->execute();
$result = $productStmt->get_result();
$product = $result->fetch_assoc();
$productStmt->close();

http_response_code(201);
echo json_encode([
    'success' => true,
    'message' => 'Ürün başarıyla eklendi.',
    'data' => $product,
], JSON_UNESCAPED_UNICODE);
