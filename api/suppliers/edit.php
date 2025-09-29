<?php

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: text/html; charset=utf-8');

    $supplier = [
        'id' => '',
        'name' => '',
        'address' => '',
        'email' => '',
        'website' => '',
        'phonenumber' => ''
    ];

    $statusClass = '';
    $statusMessage = '';

    if (isset($_GET['id']) && $_GET['id'] !== '') {
        $requestedId = (int) $_GET['id'];

        if ($requestedId > 0) {
            require_once __DIR__ . '/../../config.php';

            $stmt = $connection->prepare('SELECT id, name, address, email, COALESCE(website, \'\') AS website, phonenumber FROM suppliers WHERE id = ?');

            if ($stmt && $stmt->bind_param('i', $requestedId) && $stmt->execute()) {
                $stmt->bind_result($id, $name, $address, $email, $website, $phoneNumber);

                if ($stmt->fetch()) {
                    $supplier = [
                        'id' => (int) $id,
                        'name' => (string) $name,
                        'address' => (string) $address,
                        'email' => (string) $email,
                        'website' => (string) $website,
                        'phonenumber' => (string) $phoneNumber
                    ];
                } else {
                    $supplier['id'] = $requestedId;
                    $statusClass = 'error';
                    $statusMessage = 'Belirtilen kimlikte bir tedarikçi bulunamadı.';
                }
            } else {
                $statusClass = 'error';
                $statusMessage = 'Tedarikçi bilgileri alınırken bir hata oluştu.';
            }

            if ($stmt instanceof mysqli_stmt) {
                $stmt->close();
            }
        } else {
            $statusClass = 'error';
            $statusMessage = 'Geçerli bir tedarikçi kimliği girin.';
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Tedarikçi Güncelle</title>
        <style>
            :root {
                color-scheme: light dark;
                --bg-color: #E6F4F1;
                --card-bg: #FFFFFF;
                --primary: #008C72;
                --primary-dark: #007369;
                --danger: #003840;
                --text-color: #003840;
                --muted-text: #005A5B;
            }

            @media (prefers-color-scheme: dark) {
                :root {
                    --bg-color: #003840;
                    --card-bg: #005A5B;
                    --text-color: #E8F9F3;
                    --muted-text: #C3EDE3;
                }
            }

            body {
                margin: 0;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: var(--bg-color);
                color: var(--text-color);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 24px;
            }

            .card {
                background: var(--card-bg);
                border-radius: 18px;
                padding: 36px;
                width: min(620px, 100%);
                box-shadow: 0 25px 50px rgba(0, 56, 64, 0.2);
            }

            h1 {
                margin: 0 0 10px;
                font-size: 1.85rem;
            }

            p.description {
                margin: 0 0 28px;
                color: var(--muted-text);
                line-height: 1.55;
            }

            .form-grid {
                display: grid;
                gap: 18px;
            }

            label {
                display: block;
                font-weight: 600;
                margin-bottom: 6px;
            }

            input, textarea {
                width: 100%;
                padding: 12px 14px;
                border-radius: 10px;
                border: 1px solid rgba(0, 140, 114, 0.35);
                font-size: 1rem;
                background: transparent;
                transition: border-color 0.2s ease, box-shadow 0.2s ease;
                color: inherit;
            }

            input:focus, textarea:focus {
                outline: none;
                border-color: var(--primary);
                box-shadow: 0 0 0 4px rgba(0, 140, 114, 0.2);
            }

            .actions {
                display: flex;
                gap: 12px;
                margin-top: 8px;
                flex-wrap: wrap;
            }

            button {
                border: none;
                border-radius: 999px;
                padding: 12px 22px;
                font-size: 1rem;
                font-weight: 600;
                cursor: pointer;
                transition: transform 0.15s ease, box-shadow 0.15s ease;
            }

            button.primary {
                background: linear-gradient(135deg, var(--primary), var(--primary-dark));
                color: #FFFFFF;
                box-shadow: 0 10px 32px rgba(0, 115, 105, 0.35);
            }

            button.primary:hover {
                transform: translateY(-1px);
                box-shadow: 0 14px 36px rgba(0, 115, 105, 0.4);
            }

            .actions button:not(.primary) {
                background: rgba(0, 90, 91, 0.18);
                color: var(--text-color);
            }

            .actions button:not(.primary):hover {
                background: rgba(0, 90, 91, 0.28);
            }

            .status {
                margin-top: 18px;
                padding: 14px 16px;
                border-radius: 12px;
                font-weight: 500;
                display: none;
            }

            .status.success {
                display: block;
                background: rgba(2, 166, 118, 0.12);
                color: #007369;
            }

            .status.error {
                display: block;
                background: rgba(0, 56, 64, 0.12);
                color: var(--danger);
            }

            .status pre {
                margin: 6px 0 0;
                white-space: pre-wrap;
                font-family: 'JetBrains Mono', 'Fira Code', monospace;
            }
        </style>
    </head>
    <body>
    <div class="card">
        <h1>Tedarikçi Bilgilerini Güncelle</h1>
        <p class="description">
            Güncellemek istediğiniz tedarikçinin kimliğini ve yeni bilgilerini girin. Form, API yanıtını JSON olarak
            gösterecektir.
        </p>
        <form id="supplier-edit-form" class="form-grid">
            <div>
                <label for="id">Tedarikçi ID</label>
                <input type="number" id="id" name="id" min="1" placeholder="1" required value="<?php echo $supplier['id'] !== '' ? htmlspecialchars((string) $supplier['id'], ENT_QUOTES, 'UTF-8') : ''; ?>">
            </div>
            <div>
                <label for="name">Tedarikçi Adı</label>
                <input type="text" id="name" name="name" placeholder="Örn. ABC Lojistik" required value="<?php echo htmlspecialchars((string) $supplier['name'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div>
                <label for="address">Adres</label>
                <textarea id="address" name="address" rows="2" placeholder="Cadde, Mahalle, Şehir" required><?php echo htmlspecialchars((string) $supplier['address'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
            <div>
                <label for="email">E-posta</label>
                <input type="email" id="email" name="email" placeholder="ornek@tedarikci.com" required value="<?php echo htmlspecialchars((string) $supplier['email'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div>
                <label for="website">Web Sitesi (Opsiyonel)</label>
                <input type="url" id="website" name="website" placeholder="https://" value="<?php echo htmlspecialchars((string) $supplier['website'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div>
                <label for="phonenumber">Telefon</label>
                <input type="tel" id="phonenumber" name="phonenumber" placeholder="0 (5xx) xxx xx xx" required value="<?php echo htmlspecialchars((string) $supplier['phonenumber'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="actions">
                <button type="submit" class="primary">Bilgileri Güncelle</button>
                <button type="reset">Temizle</button>
            </div>
        </form>
        <div class="status<?php echo $statusClass !== '' ? ' ' . htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8') : ''; ?>" id="status"<?php echo $statusMessage === '' ? ' style="display: none;"' : ''; ?>><?php echo htmlspecialchars($statusMessage, ENT_QUOTES, 'UTF-8'); ?></div>
    </div>

    <script>
        const form = document.getElementById('supplier-edit-form');
        const statusBox = document.getElementById('status');

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            statusBox.className = 'status';
            statusBox.textContent = 'Gönderiliyor...';
            statusBox.style.display = 'block';

            const formData = Object.fromEntries(new FormData(form));

            try {
                const response = await fetch(window.location.href, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });

                const result = await response.json();

                statusBox.classList.add(response.ok && result.success ? 'success' : 'error');
                statusBox.innerHTML = `<strong>${result.message || 'Bir sorun oluştu.'}</strong>`;

                if (result.data) {
                    const pretty = JSON.stringify(result.data, null, 2);
                    statusBox.innerHTML += `<pre>${pretty}</pre>`;
                }
            } catch (error) {
                statusBox.classList.add('error');
                statusBox.textContent = 'Sunucuya ulaşılamadı. Lütfen daha sonra tekrar deneyin.';
            }
        });
    </script>
    </body>
    </html>
    <?php
    exit;
}

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config.php';

$allowedMethods = ['POST', 'PUT', 'PATCH'];

if (!in_array($_SERVER['REQUEST_METHOD'], $allowedMethods, true)) {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'İstek yöntemi desteklenmiyor. POST, PUT veya PATCH kullanın.'
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

$id = isset($data['id']) ? (int) $data['id'] : 0;

if ($id <= 0) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Geçerli bir tedarikçi kimliği belirtilmelidir.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
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

$stmt = $connection->prepare("UPDATE suppliers SET name = ?, address = ?, email = ?, website = NULLIF(?, ''), phonenumber = ? WHERE id = ?");

if ($stmt === false) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Veritabanı hatası: ' . $connection->error
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt->bind_param('sssssi', $name, $address, $email, $website, $phone, $id);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Tedarikçi güncellenemedi: ' . $stmt->error
    ], JSON_UNESCAPED_UNICODE);
    $stmt->close();
    exit;
}

if ($stmt->affected_rows === 0) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'Tedarikçi bulunamadı veya herhangi bir değişiklik yapılmadı.'
    ], JSON_UNESCAPED_UNICODE);
    $stmt->close();
    exit;
}

$stmt->close();

echo json_encode([
    'success' => true,
    'message' => 'Tedarikçi başarıyla güncellendi.',
    'data' => [
        'id' => $id,
        'name' => $name,
        'address' => $address,
        'email' => $email,
        'website' => $website,
        'phonenumber' => $phone
    ]
], JSON_UNESCAPED_UNICODE);
