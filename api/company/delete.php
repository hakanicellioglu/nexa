<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response([
        'success' => false,
        'message' => 'Yalnızca POST isteklerine izin verilir.',
    ], 405);
}

$user = require_api_user();

$token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
if (!validate_csrf_token(is_string($token) ? $token : null)) {
    json_response([
        'success' => false,
        'message' => 'CSRF doğrulaması başarısız oldu.',
    ], 400);
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
if ($id <= 0) {
    json_response([
        'success' => false,
        'message' => 'Geçersiz şirket kimliği.',
    ], 422);
}

$pdo = get_db_connection();
$companyStmt = $pdo->prepare('SELECT id FROM company WHERE id = :id AND user_id = :user_id LIMIT 1');
$companyStmt->execute([
    ':id' => $id,
    ':user_id' => $user['id'],
]);

if (!$companyStmt->fetch()) {
    json_response([
        'success' => false,
        'message' => 'Şirket kaydı bulunamadı.',
    ], 404);
}

$deleteStmt = $pdo->prepare('DELETE FROM company WHERE id = :id');
$deleteStmt->execute([':id' => $id]);

json_response([
    'success' => true,
    'message' => 'Şirket kaydı silindi.',
]);
