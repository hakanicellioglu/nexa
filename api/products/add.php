<?php

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Ürün Ekle</title>
        <style>
            :root {
                color-scheme: light dark;
                --bg-color: #e6f4f1;
                --card-bg: #ffffff;
                --primary: #008c72;
                --primary-dark: #007369;
                --danger: #003840;
                --text-color: #003840;
                --muted-text: #005a5b;
            }

            @media (prefers-color-scheme: dark) {
                :root {
                    --bg-color: #002b36;
                    --card-bg: #005a5b;
                    --text-color: #e8f9f3;
                    --muted-text: #c3ede3;
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
                border-radius: 16px;
                box-shadow: 0 20px 45px rgba(0, 56, 64, 0.15);
                padding: 32px;
                width: min(520px, 100%);
            }

            h1 {
                margin-top: 0;
                font-size: 1.75rem;
                letter-spacing: -0.02em;
            }

            p.description {
                margin: 8px 0 24px;
                color: var(--muted-text);
                line-height: 1.5;
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

            input {
                width: 100%;
                padding: 12px 14px;
                border-radius: 10px;
                border: 1px solid rgba(0, 140, 114, 0.35);
                font-size: 1rem;
                transition: border-color 0.2s ease, box-shadow 0.2s ease;
                background: transparent;
                color: inherit;
            }

            input:focus {
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
                color: #ffffff;
                box-shadow: 0 10px 30px rgba(0, 115, 105, 0.35);
            }

            button.primary:hover {
                transform: translateY(-1px);
                box-shadow: 0 16px 40px rgba(0, 115, 105, 0.45);
            }

            .actions button:not(.primary) {
                background: rgba(0, 90, 91, 0.18);
                color: var(--text-color);
            }

            .actions button:not(.primary):hover {
                background: rgba(0, 90, 91, 0.28);
            }

            .status {
                margin-top: 16px;
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
                margin: 4px 0 0;
                white-space: pre-wrap;
                font-family: 'JetBrains Mono', 'Fira Code', monospace;
            }
        </style>
    </head>
    <body>
    <div class="card">
        <h1>Ürün Ekle</h1>
        <p class="description">
            Yeni ürünleri Nexa veritabanına eklemek için aşağıdaki formu kullanın. Form başarıyla gönderildiğinde sonuç bu sayfada gösterilecektir.
        </p>
        <form id="product-form" class="form-grid">
            <div>
                <label for="name">Ürün Adı</label>
                <input type="text" id="name" name="name" placeholder="Örn. Akıllı Sensör" required>
            </div>
            <div>
                <label for="type">Ürün Türü</label>
                <input type="text" id="type" name="type" placeholder="Örn. Donanım" required>
            </div>
            <div class="actions">
                <button type="submit" class="primary">Ürünü Kaydet</button>
                <button type="reset">Temizle</button>
            </div>
        </form>
        <div class="status" id="status"></div>
    </div>

    <script>
        const form = document.getElementById('product-form');
        const statusBox = document.getElementById('status');

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            statusBox.className = 'status';
            statusBox.textContent = 'Gönderiliyor...';
            statusBox.style.display = 'block';

            const formData = Object.fromEntries(new FormData(form));

            try {
                const response = await fetch(window.location.href, {
                    method: 'POST',
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

                if (response.ok && result.success) {
                    form.reset();
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

$name = isset($data['name']) ? trim((string) $data['name']) : '';
$type = isset($data['type']) ? trim((string) $data['type']) : '';

if ($name === '' || $type === '') {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Ürün adı ve türü zorunludur.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$tableCheck = $connection->query("SHOW TABLES LIKE 'products'");
if ($tableCheck === false || $tableCheck->num_rows === 0) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Products tablosu bulunamadı. Lütfen veritabanını güncelleyin.'
    ], JSON_UNESCAPED_UNICODE);
    if ($tableCheck instanceof mysqli_result) {
        $tableCheck->free();
    }
    exit;
}

if ($tableCheck instanceof mysqli_result) {
    $tableCheck->free();
}

$stmt = $connection->prepare('INSERT INTO products (name, type) VALUES (?, ?)');

if ($stmt === false) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Veritabanı hatası: ' . $connection->error
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt->bind_param('ss', $name, $type);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Ürün eklenemedi: ' . $stmt->error
    ], JSON_UNESCAPED_UNICODE);
    $stmt->close();
    exit;
}

$newId = $connection->insert_id;
$stmt->close();

http_response_code(201);

echo json_encode([
    'success' => true,
    'message' => 'Ürün başarıyla oluşturuldu.',
    'data' => [
        'id' => $newId,
        'name' => $name,
        'type' => $type,
    ]
], JSON_UNESCAPED_UNICODE);
