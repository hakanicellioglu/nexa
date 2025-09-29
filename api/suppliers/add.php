<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'İstek yöntemi desteklenmiyor. POST kullanın.'
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

$requiredFields = ['name', 'address', 'email', 'phonenumber'];
$missingFields = [];

foreach ($requiredFields as $field) {
    if (empty($data[$field]) || !is_string($data[$field])) {
        $missingFields[] = $field;
    }
}

if (!empty($missingFields)) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Eksik veya hatalı alanlar: ' . implode(', ', $missingFields)
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$name = trim($data['name']);
$address = trim($data['address']);
$email = trim($data['email']);
$website = isset($data['website']) ? trim((string) $data['website']) : null;

if ($website === null) {
    $website = '';
}
$phone = trim($data['phonenumber']);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Geçerli bir e-posta adresi girin.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($website !== null && $website !== '' && !filter_var($website, FILTER_VALIDATE_URL)) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Geçerli bir web sitesi adresi girin.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt = $connection->prepare("INSERT INTO suppliers (name, address, email, website, phonenumber) VALUES (?, ?, ?, NULLIF(?, ''), ?)");

if ($stmt === false) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Veritabanı hatası: ' . $connection->error
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt->bind_param('sssss', $name, $address, $email, $website, $phone);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Tedarikçi eklenemedi: ' . $stmt->error
    ], JSON_UNESCAPED_UNICODE);
    $stmt->close();
    exit;
}

$newId = $connection->insert_id;
$stmt->close();

http_response_code(201);
echo json_encode([
    'success' => true,
    'message' => 'Tedarikçi başarıyla oluşturuldu.',
    'data' => [
        'id' => $newId,
        'name' => $name,
        'address' => $address,
        'email' => $email,
        'website' => $website,
        'phonenumber' => $phone
    ]
], JSON_UNESCAPED_UNICODE);
