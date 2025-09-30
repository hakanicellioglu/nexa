<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config.php';

$allowedMethods = ['POST', 'DELETE'];
if (! in_array($_SERVER['REQUEST_METHOD'], $allowedMethods, true)) {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Yalnızca POST veya DELETE isteklerine izin verilmektedir.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);
if (! is_array($data)) {
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

$deleteStmt = $mysqli->prepare('DELETE FROM products WHERE id = ?');
if (! $deleteStmt) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Ürün silinirken bir hata oluştu.',
        'details' => $mysqli->error,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$deleteStmt->bind_param('i', $id);
$deleteStmt->execute();

if ($deleteStmt->affected_rows === 0) {
    $deleteStmt->close();
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'Ürün bulunamadı veya zaten silinmiş.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$deleteStmt->close();

http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'Ürün başarıyla silindi.'
], JSON_UNESCAPED_UNICODE);
