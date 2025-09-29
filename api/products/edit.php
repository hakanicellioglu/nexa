<?php

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: text/html; charset=utf-8');

    $product = [
        'id' => '',
        'name' => '',
        'type' => ''
    ];

    $statusClass = '';
    $statusMessage = '';

    if (isset($_GET['id']) && $_GET['id'] !== '') {
        $requestedId = (int) $_GET['id'];

        if ($requestedId > 0) {
            require_once __DIR__ . '/../../config.php';

            $stmt = $connection->prepare('SELECT id, name, type FROM products WHERE id = ?');

            if ($stmt && $stmt->bind_param('i', $requestedId) && $stmt->execute()) {
                $stmt->bind_result($id, $name, $type);

                if ($stmt->fetch()) {
                    $product = [
                        'id' => (int) $id,
                        'name' => (string) $name,
                        'type' => (string) $type,
                    ];
                } else {
                    $product['id'] = $requestedId;
                    $statusClass = 'error';
                    $statusMessage = 'Belirtilen kimlikte bir ürün bulunamadı.';
                }
            } else {
                $statusClass = 'error';
                $statusMessage = 'Ürün bilgileri alınırken bir hata oluştu.';
            }

            if ($stmt instanceof mysqli_stmt) {
                $stmt->close();
            }
        } else {
            $statusClass = 'error';
            $statusMessage = 'Geçerli bir ürün kimliği girin.';
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Ürün Güncelle</title>
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
                border-radius: 18px;
                padding: 36px;
                width: min(560px, 100%);
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

            input {
                width: 100%;
                padding: 12px 14px;
                border-radius: 10px;
                border: 1px solid rgba(0, 140, 114, 0.35);
                font-size: 1rem;
                background: transparent;
                transition: border-color 0.2s ease, box-shadow 0.2s ease;
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
                margin: 4px 0 0;
                white-space: pre-wrap;
                font-family: 'JetBrains Mono', 'Fira Code', monospace;
            }
        </style>
    </head>
    <body>
    <div class="card">
        <h1>Ürün Güncelle</h1>
        <p class="description">Bir ürünün adı veya türünü güncellemek için bu formu kullanın. Form gönderildiğinde sonuçlar aşağıda görüntülenecektir.</p>
        <form id="product-form" class="form-grid">
            <input type="hidden" id="id" name="id" value="<?php echo htmlspecialchars((string) $product['id'], ENT_QUOTES, 'UTF-8'); ?>">
            <div>
                <label for="name">Ürün Adı</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars((string) $product['name'], ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div>
                <label for="type">Ürün Türü</label>
                <input type="text" id="type" name="type" value="<?php echo htmlspecialchars((string) $product['type'], ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div class="actions">
                <button type="submit" class="primary">Ürünü Güncelle</button>
                <button type="reset">Temizle</button>
            </div>
        </form>
        <div class="status <?php echo $statusClass; ?>" id="status">
            <?php if ($statusMessage !== ''): ?>
                <strong><?php echo htmlspecialchars($statusMessage, ENT_QUOTES, 'UTF-8'); ?></strong>
            <?php endif; ?>
        </div>
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

$id = isset($data['id']) ? (int) $data['id'] : 0;
$name = isset($data['name']) ? trim((string) $data['name']) : '';
$type = isset($data['type']) ? trim((string) $data['type']) : '';

if ($id <= 0) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Geçerli bir ürün kimliği belirtilmelidir.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

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

$stmt = $connection->prepare('UPDATE products SET name = ?, type = ? WHERE id = ?');

if ($stmt === false) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Veritabanı hatası: ' . $connection->error
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt->bind_param('ssi', $name, $type, $id);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Ürün güncellenemedi: ' . $stmt->error
    ], JSON_UNESCAPED_UNICODE);
    $stmt->close();
    exit;
}

if ($stmt->affected_rows === 0) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'Güncellenecek ürün bulunamadı.'
    ], JSON_UNESCAPED_UNICODE);
    $stmt->close();
    exit;
}

$stmt->close();

echo json_encode([
    'success' => true,
    'message' => 'Ürün başarıyla güncellendi.',
    'data' => [
        'id' => $id,
        'name' => $name,
        'type' => $type,
    ]
], JSON_UNESCAPED_UNICODE);
