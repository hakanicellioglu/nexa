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

$ad = trim($_POST['ad'] ?? '');
$gorev = trim($_POST['gorev'] ?? '');
$telefon = trim($_POST['telefon'] ?? '');
$eposta = trim($_POST['eposta'] ?? '');
$aktifRaw = $_POST['aktif'] ?? '1';
$aktif = (string) $aktifRaw === '0' ? 0 : 1;

if ($ad === '') {
    json_response([
        'success' => false,
        'message' => 'İsim alanı zorunludur.',
    ], 422);
}

if ($eposta !== '' && !filter_var($eposta, FILTER_VALIDATE_EMAIL)) {
    json_response([
        'success' => false,
        'message' => 'Geçerli bir e-posta adresi giriniz.',
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
        'message' => 'Şirket kaydınız bulunamadı. Önce şirket bilgilerinizi oluşturmalısınız.',
    ], 404);
}

$insertStmt = $pdo->prepare('INSERT INTO company_contacts (company_id, ad, gorev, telefon, eposta, aktif) VALUES (:company_id, :ad, :gorev, :telefon, :eposta, :aktif)');
$insertStmt->execute([
    ':company_id' => $company['id'],
    ':ad' => $ad,
    ':gorev' => $gorev !== '' ? $gorev : null,
    ':telefon' => $telefon !== '' ? $telefon : null,
    ':eposta' => $eposta !== '' ? $eposta : null,
    ':aktif' => $aktif,
]);

$contactId = (int) $pdo->lastInsertId();

json_response([
    'success' => true,
    'message' => 'Kişi başarıyla eklendi.',
    'contact_id' => $contactId,
]);
