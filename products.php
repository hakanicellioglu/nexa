<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/header.php';

/**
 * Check whether the products table exists.
 */
function nexaProductsTableExists(mysqli $connection): bool
{
    $escapedTable = $connection->real_escape_string('products');
    $query = "SHOW TABLES LIKE '{$escapedTable}'";
    $result = $connection->query($query);

    if ($result === false) {
        return false;
    }

    $exists = $result->num_rows > 0;
    $result->free();

    return $exists;
}

/**
 * Fetch all products from the database.
 *
 * @return array<int, array<string, string|null>>
 */
function nexaFetchProducts(mysqli $connection): array
{
    if (!nexaProductsTableExists($connection)) {
        return [];
    }

    $sql = 'SELECT id, name, type, created_at FROM products ORDER BY name ASC';
    $result = $connection->query($sql);

    if ($result === false) {
        return [];
    }

    $products = [];

    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }

    $result->free();

    return $products;
}

$products = nexaFetchProducts($connection);
$totalProducts = count($products);
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Ürünler - Nexa</title>
    <style>
        :root {
            color-scheme: light dark;
        }

        body {
            margin: 0;
            font-family: "Inter", Arial, sans-serif;
            background: #002b36;
            color: #e5f8f3;
        }

        main {
            padding: 2.5rem;
            max-width: 1120px;
            margin: 0 auto;
            display: grid;
            gap: 2rem;
        }

        .hero {
            background: linear-gradient(135deg, rgba(0, 140, 114, 0.22), rgba(2, 166, 118, 0.2));
            border: 1px solid rgba(2, 166, 118, 0.35);
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 18px 50px rgba(0, 43, 54, 0.45);
        }

        .hero h1 {
            margin: 0 0 0.75rem;
            font-size: 2.25rem;
            letter-spacing: 0.01em;
        }

        .hero p {
            margin: 0;
            color: #b6e6d9;
            font-size: 1.1rem;
            max-width: 720px;
            line-height: 1.6;
        }

        .stat {
            margin-top: 1.5rem;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            background: rgba(0, 56, 64, 0.6);
            padding: 0.85rem 1.25rem;
            border-radius: 999px;
            border: 1px solid rgba(2, 166, 118, 0.45);
            color: #d4fff2;
            font-weight: 600;
        }

        .table-card {
            background: rgba(0, 72, 74, 0.92);
            border-radius: 18px;
            border: 1px solid rgba(2, 166, 118, 0.25);
            box-shadow: 0 16px 35px rgba(0, 43, 54, 0.45);
            overflow: hidden;
        }

        .table-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid rgba(2, 166, 118, 0.25);
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .table-header h2 {
            margin: 0 0 0.5rem;
            font-size: 1.5rem;
        }

        .table-actions {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
        }

        .table-actions button {
            border: none;
            border-radius: 999px;
            padding: 0.55rem 1.1rem;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
        }

        .table-actions button:focus-visible {
            outline: 2px solid rgba(2, 166, 118, 0.65);
            outline-offset: 2px;
        }

        .action-add {
            background: linear-gradient(135deg, rgba(0, 140, 114, 0.95), rgba(2, 166, 118, 0.95));
            color: #003840;
            box-shadow: 0 10px 20px rgba(0, 115, 105, 0.35);
        }

        .action-edit {
            background: rgba(0, 115, 105, 0.25);
            color: #02a676;
        }

        .action-delete {
            background: rgba(0, 56, 64, 0.25);
            color: #e8f9f3;
        }

        .table-actions button:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 18px rgba(0, 43, 54, 0.25);
        }

        .table-actions button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: rgba(0, 72, 74, 0.8);
        }

        th,
        td {
            padding: 1rem 1.5rem;
            text-align: left;
            border-bottom: 1px solid rgba(148, 163, 184, 0.12);
        }

        th {
            font-weight: 600;
            color: #cffceb;
            font-size: 0.95rem;
        }

        td {
            color: #e8f9f3;
            font-size: 0.95rem;
        }

        tbody tr:hover {
            background: rgba(0, 140, 114, 0.12);
        }

        .empty-state {
            padding: 2.5rem;
            text-align: center;
            color: #c3ede3;
            font-size: 1rem;
        }

        @media (max-width: 768px) {
            main {
                padding: 1.5rem;
            }

            th,
            td {
                padding: 0.75rem 1rem;
            }

            .hero {
                padding: 2rem;
            }
        }
    </style>
</head>

<body>
    <?php renderNexaHeader(); ?>
    <main>
        <section class="hero" id="urunler">
            <h1>Ürün Envanteri</h1>
            <p>Nexa ekosisteminde kayıtlı ürünleri görüntüleyin ve ürün tiplerine göre hızlıca filtreleyin. Yeni ürünler ekleyerek portföyünüzü güncel tutabilirsiniz.</p>
            <div class="stat">
                Toplam Ürün: <?php echo number_format($totalProducts, 0, ',', '.'); ?>
            </div>
        </section>

        <section class="table-card" aria-labelledby="product-list-heading">
            <div class="table-header">
                <div>
                    <h2 id="product-list-heading">Ürün Listesi</h2>
                    <p>Kayıtlı tüm ürünler ve tipleri.</p>
                </div>
                <div class="table-actions">
                    <button type="button" class="action-add" id="add-product-button">Yeni Ürün Ekle</button>
                </div>
            </div>
            <?php if ($totalProducts > 0): ?>
                <div class="table-wrapper">
                    <table aria-describedby="product-list-heading">
                        <thead>
                            <tr>
                                <th scope="col">Adı</th>
                                <th scope="col">Türü</th>
                                <th scope="col">Kayıt Tarihi</th>
                                <th scope="col">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($product['type'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                        <?php
                                        $timestamp = $product['created_at'] ?? '';
                                        $formatted = '—';

                                        if (!empty($timestamp)) {
                                            try {
                                                $date = new DateTime($timestamp);
                                                $formatted = $date->format('d.m.Y H:i');
                                            } catch (Exception $exception) {
                                                $formatted = '—';
                                            }
                                        }

                                        echo $formatted === '—'
                                            ? $formatted
                                            : htmlspecialchars($formatted, ENT_QUOTES, 'UTF-8');
                                        ?>
                                    </td>
                                    <td>
                                        <div class="table-actions" data-product-row data-product='<?php echo json_encode([
                                            'id' => (int) $product['id'],
                                            'name' => $product['name'],
                                            'type' => $product['type'],
                                        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>'>
                                            <button type="button" class="action-edit" data-action="edit">Güncelle</button>
                                            <button type="button" class="action-delete" data-action="delete">Sil</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    Henüz kayıtlı ürün bulunmuyor veya ürünler tablosu oluşturulmadı. Yeni ürünler eklemek için veritabanını güncelleyin.
                </div>
            <?php endif; ?>
        </section>
    </main>
    <script>
        (function () {
            const addButton = document.getElementById('add-product-button');
            const rows = document.querySelectorAll('[data-product-row]');

            /**
             * @param {string} message
             * @param {string} [defaultValue]
             * @returns {string|null}
             */
            function promptField(message, defaultValue = '') {
                const response = window.prompt(message, defaultValue);

                if (response === null) {
                    return null;
                }

                return response.trim();
            }

            /**
             * @param {{name?: string, type?: string}} defaults
             * @returns {{name: string, type: string}|null}
             */
            function collectProductData(defaults) {
                const name = promptField('Ürün adı', defaults.name || '');
                if (!name) {
                    return null;
                }

                const type = promptField('Ürün türü', defaults.type || '');
                if (!type) {
                    return null;
                }

                return { name, type };
            }

            async function sendRequest(url, payload) {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    const message = result.message || 'Bir hata oluştu.';
                    window.alert(message);
                    return false;
                }

                return true;
            }

            addButton?.addEventListener('click', async () => {
                const payload = collectProductData({});
                if (!payload) {
                    return;
                }

                const ok = await sendRequest('api/products/add.php', payload);
                if (ok) {
                    window.location.reload();
                }
            });

            rows.forEach((row) => {
                const product = JSON.parse(row.getAttribute('data-product'));
                const editButton = row.querySelector('[data-action="edit"]');
                const deleteButton = row.querySelector('[data-action="delete"]');

                editButton?.addEventListener('click', async () => {
                    const payload = collectProductData(product);
                    if (!payload) {
                        return;
                    }

                    payload.id = product.id;

                    const ok = await sendRequest('api/products/edit.php', payload);
                    if (ok) {
                        window.location.reload();
                    }
                });

                deleteButton?.addEventListener('click', async () => {
                    const confirmation = window.confirm(`\n${product.name} ürününü silmek istediğinize emin misiniz?`);
                    if (!confirmation) {
                        return;
                    }

                    const ok = await sendRequest('api/products/delete.php', { id: product.id });
                    if (ok) {
                        window.location.reload();
                    }
                });
            });
        })();
    </script>
</body>

</html>
