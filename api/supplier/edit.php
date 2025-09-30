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
$name = isset($payload['name']) ? trim($payload['name']) : '';

if ($id <= 0) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Geçerli bir tedarikçi ID gerekli.',
        'errors' => ['id' => 'Geçerli bir tedarikçi ID belirtilmelidir.'],
    ]);
    exit;
}

if ($name === '') {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Tedarikçi adı gerekli.',
        'errors' => ['name' => 'Tedarikçi adı boş olamaz.'],
    ]);
    exit;
}

$stmt = $mysqli->prepare('UPDATE suppliers SET name = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');

if (! $stmt) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Veritabanı hatası: ' . $mysqli->error,
    ]);
    exit;
}

$stmt->bind_param('si', $name, $id);

if (! $stmt->execute()) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Tedarikçi güncellenemedi. Lütfen tekrar deneyin.',
    ]);
    $stmt->close();
    exit;
}

if ($stmt->affected_rows === 0) {
    // Güncelleme yapılmadıysa kaydın var olup olmadığını kontrol et.
    $checkStmt = $mysqli->prepare('SELECT id FROM suppliers WHERE id = ?');
    if ($checkStmt) {
        $checkStmt->bind_param('i', $id);
        $checkStmt->execute();
        $checkStmt->store_result();
        if ($checkStmt->num_rows === 0) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Tedarikçi bulunamadı.',
            ]);
            $checkStmt->close();
            $stmt->close();
            exit;
        }
        $checkStmt->close();
    }
}

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

$selectStmt->bind_param('i', $id);
$selectStmt->execute();
$result = $selectStmt->get_result();
$supplierData = $result ? $result->fetch_assoc() : null;
$selectStmt->close();

if (! $supplierData) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'Tedarikçi bulunamadı.',
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'Tedarikçi başarıyla güncellendi.',
    'supplier' => $supplierData,
]);
