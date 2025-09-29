<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/header.php';

/**
 * Check if the suppliers table exists in the database.
 */
function nexaSuppliersTableExists(mysqli $connection): bool
{
    $escapedTable = $connection->real_escape_string('suppliers');
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
 * Retrieve suppliers from the database ordered by name.
 *
 * @return array<int, array<string, string|null>>
 */
function nexaFetchSuppliers(mysqli $connection): array
{
    if (!nexaSuppliersTableExists($connection)) {
        return [];
    }

    $sql = 'SELECT id, name, address, email, website, phonenumber, created_at FROM suppliers ORDER BY name ASC';
    $result = $connection->query($sql);

    if ($result === false) {
        return [];
    }

    $suppliers = [];

    while ($row = $result->fetch_assoc()) {
        $suppliers[] = $row;
    }

    $result->free();

    return $suppliers;
}

$suppliers = nexaFetchSuppliers($connection);
$totalSuppliers = count($suppliers);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Tedarikçiler - Nexa</title>
    <style>
        :root {
            color-scheme: light dark;
        }

        body {
            margin: 0;
            font-family: "Inter", Arial, sans-serif;
            background: #0f172a;
            color: #f8fafc;
        }

        main {
            padding: 2.5rem;
            max-width: 1120px;
            margin: 0 auto;
            display: grid;
            gap: 2rem;
        }

        .hero {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.15), rgba(236, 72, 153, 0.12));
            border: 1px solid rgba(148, 163, 184, 0.18);
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 20px 55px rgba(15, 23, 42, 0.35);
        }

        .hero h1 {
            margin: 0 0 0.75rem;
            font-size: 2.25rem;
            letter-spacing: 0.01em;
        }

        .hero p {
            margin: 0;
            color: #cbd5f5;
            font-size: 1.1rem;
            max-width: 720px;
            line-height: 1.6;
        }

        .stat {
            margin-top: 1.5rem;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            background: rgba(15, 23, 42, 0.6);
            padding: 0.85rem 1.25rem;
            border-radius: 999px;
            border: 1px solid rgba(99, 102, 241, 0.45);
            color: #e0e7ff;
            font-weight: 600;
        }

        .table-card {
            background: rgba(15, 23, 42, 0.92);
            border-radius: 18px;
            border: 1px solid rgba(148, 163, 184, 0.2);
            box-shadow: 0 16px 35px rgba(15, 23, 42, 0.35);
            overflow: hidden;
        }

        .table-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid rgba(148, 163, 184, 0.18);
        }

        .table-header h2 {
            margin: 0 0 0.5rem;
            font-size: 1.5rem;
        }

        .table-header p {
            margin: 0;
            color: #94a3b8;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: rgba(30, 41, 59, 0.8);
        }

        th,
        td {
            padding: 1rem 1.5rem;
            text-align: left;
            border-bottom: 1px solid rgba(148, 163, 184, 0.12);
        }

        th {
            font-weight: 600;
            color: #cbd5f5;
            font-size: 0.95rem;
        }

        td {
            color: #e2e8f0;
            font-size: 0.95rem;
        }

        tbody tr:hover {
            background: rgba(99, 102, 241, 0.08);
        }

        .empty-state {
            padding: 2.5rem;
            text-align: center;
            color: #94a3b8;
            font-size: 1rem;
        }

        a.supplier-link {
            color: #60a5fa;
            text-decoration: none;
        }

        a.supplier-link:hover,
        a.supplier-link:focus {
            text-decoration: underline;
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
    <section class="hero" id="tedarikciler">
        <h1>Tedarikçi Kataloğu</h1>
        <p>Nexa ekosistemine kayıtlı tüm tedarikçileri görüntüleyin ve iletişim bilgilerine hızla erişin. Bu liste, toplu satın alma stratejilerinizi planlamanıza yardımcı olacak şekilde düzenlenmiştir.</p>
        <div class="stat">
            Toplam Tedarikçi: <?php echo number_format($totalSuppliers, 0, ',', '.'); ?>
        </div>
    </section>

    <section class="table-card" aria-labelledby="supplier-list-heading">
        <div class="table-header">
            <h2 id="supplier-list-heading">Tedarikçi Listesi</h2>
            <p>İletişim bilgileri ve web siteleri dahil olmak üzere tüm kayıtlı tedarikçiler.</p>
        </div>
        <?php if ($totalSuppliers > 0): ?>
            <div class="table-wrapper">
                <table aria-describedby="supplier-list-heading">
                    <thead>
                        <tr>
                            <th scope="col">Adı</th>
                            <th scope="col">Adres</th>
                            <th scope="col">E-posta</th>
                            <th scope="col">Web Sitesi</th>
                            <th scope="col">Telefon</th>
                            <th scope="col">Kayıt Tarihi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($suppliers as $supplier): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($supplier['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($supplier['address'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <a class="supplier-link" href="mailto:<?php echo htmlspecialchars($supplier['email'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo htmlspecialchars($supplier['email'], ENT_QUOTES, 'UTF-8'); ?>
                                    </a>
                                </td>
                                <td>
                                    <?php if (!empty($supplier['website'])): ?>
                                        <a class="supplier-link" href="<?php echo htmlspecialchars($supplier['website'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">Web sitesine git</a>
                                    <?php else: ?>
                                        <span>—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($supplier['phonenumber'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <?php
                                    $timestamp = $supplier['created_at'] ?? '';
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
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                Henüz kayıtlı tedarikçi bulunmuyor veya tedarikçiler tablosu oluşturulmadı. Yeni tedarikçiler eklemek için veritabanını güncelleyin.
            </div>
        <?php endif; ?>
    </section>
</main>
</body>
</html>