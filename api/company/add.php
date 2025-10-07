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

$name = trim($_POST['name'] ?? '');
$adres = trim($_POST['adres'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$fax = trim($_POST['fax'] ?? '');
$website = trim($_POST['website'] ?? '');

if ($name === '') {
    json_response([
        'success' => false,
        'message' => 'Şirket adı zorunludur.',
    ], 422);
}

$pdo = get_db_connection();
$existingStmt = $pdo->prepare('SELECT COUNT(*) FROM company WHERE user_id = :user_id');
$existingStmt->execute([':user_id' => $user['id']]);

if ((int) $existingStmt->fetchColumn() > 0) {
    json_response([
        'success' => false,
        'message' => 'Zaten bir şirket kaydınız bulunuyor. Güncelleme yapabilirsiniz.',
    ], 409);
}

$insertStmt = $pdo->prepare('INSERT INTO company (user_id, name, adres, phone, fax, website) VALUES (:user_id, :name, :adres, :phone, :fax, :website)');
$insertStmt->execute([
    ':user_id' => $user['id'],
    ':name' => $name,
    ':adres' => $adres !== '' ? $adres : null,
    ':phone' => $phone !== '' ? $phone : null,
    ':fax' => $fax !== '' ? $fax : null,
    ':website' => $website !== '' ? $website : null,
]);

json_response([
    'success' => true,
    'message' => 'Şirket kaydı başarıyla oluşturuldu.',
    'company_id' => (int) $pdo->lastInsertId(),
]);
