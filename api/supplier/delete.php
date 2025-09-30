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

$id = isset($payload['id']) ? (int) $payload['id'] : 0;

if ($id <= 0) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Geçerli bir tedarikçi ID gerekli.',
        'errors' => ['id' => 'Geçerli bir tedarikçi ID belirtilmelidir.'],
    ]);
    exit;
}

$stmt = $mysqli->prepare('DELETE FROM suppliers WHERE id = ?');

if (! $stmt) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Veritabanı hatası: ' . $mysqli->error,
    ]);
    exit;
}

$stmt->bind_param('i', $id);

if (! $stmt->execute()) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Tedarikçi silinemedi. Lütfen tekrar deneyin.',
    ]);
    $stmt->close();
    exit;
}

if ($stmt->affected_rows === 0) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'Tedarikçi bulunamadı.',
    ]);
    $stmt->close();
    exit;
}

$stmt->close();

echo json_encode([
    'success' => true,
    'message' => 'Tedarikçi başarıyla silindi.',
]);
