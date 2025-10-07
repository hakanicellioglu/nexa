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
        'message' => 'Geçersiz kişi kimliği.',
    ], 422);
}

$pdo = get_db_connection();

$companyStmt = $pdo->prepare('SELECT id FROM company WHERE user_id = :user_id LIMIT 1');
$companyStmt->execute([
    ':user_id' => $user['id'],
]);
$company = $companyStmt->fetch();

if (!$company) {
    json_response([
        'success' => false,
        'message' => 'Şirket kaydınız bulunamadı.',
    ], 404);
}

$deleteStmt = $pdo->prepare('DELETE FROM company_contacts WHERE id = :id AND company_id = :company_id');
$deleteStmt->execute([
    ':id' => $id,
    ':company_id' => $company['id'],
]);

if ($deleteStmt->rowCount() === 0) {
    json_response([
        'success' => false,
        'message' => 'Kişi kaydı bulunamadı.',
    ], 404);
}

json_response([
    'success' => true,
    'message' => 'Kişi silindi.',
]);
