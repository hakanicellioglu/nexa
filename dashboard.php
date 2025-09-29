<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/header.php';

/**
 * Verilen tablo adının veritabanında bulunup bulunmadığını kontrol eder.
 */
function nexaTableExists(mysqli $connection, string $tableName): bool
{
    $escapedTable = $connection->real_escape_string($tableName);
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
 * Bir tablo için COUNT(*) sorgusunu çalıştırarak toplam değeri döndürür.
 */
function nexaFetchCount(mysqli $connection, string $tableName, ?string $where = null): ?int
{
    if (!nexaTableExists($connection, $tableName)) {
        return null;
    }

    $escapedTable = sprintf('`%s`', $connection->real_escape_string($tableName));
    $query = "SELECT COUNT(*) AS total FROM {$escapedTable}";

    if ($where !== null && $where !== '') {
        $query .= " WHERE {$where}";
    }

    $result = $connection->query($query);
    if ($result === false) {
        return null;
    }

    $row = $result->fetch_assoc();
    $result->free();

    if (!isset($row['total'])) {
        return null;
    }

    return (int) $row['total'];
}

$metricDefinitions = [
    [
        'label' => 'Aktif Ürünler',
        'table' => 'products',
        'where' => "status = 'active'",
    ],
    [
        'label' => 'Aylık Siparişler',
        'table' => 'orders',
        'where' => null,
    ],
    [
        'label' => 'Bekleyen Projeler',
        'table' => 'projects',
        'where' => "status = 'pending'",
    ],
    [
        'label' => 'Tedarikçi Sayısı',
        'table' => 'suppliers',
        'where' => null,
    ],
];

$metricData = array_map(
    static function (array $definition) use ($connection): array {
        $value = nexaFetchCount($connection, $definition['table'], $definition['where']);

        return [
            'label' => $definition['label'],
            'value' => $value,
        ];
    },
    $metricDefinitions
);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Nexa Kontrol Paneli</title>
    <style>
        :root {
            color-scheme: light dark;
        }

        body {
            margin: 0;
            font-family: "Inter", Arial, sans-serif;
            background: #282828;
            color: #ffffff;
        }

        main {
            padding: 2.5rem;
            display: grid;
            gap: 2rem;
        }

        .intro {
            background: linear-gradient(135deg, rgba(70, 70, 70, 0.35), rgba(125, 125, 125, 0.25));
            border: 1px solid rgba(225, 225, 225, 0.12);
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 20px 55px rgba(40, 40, 40, 0.35);
        }

        .intro h1 {
            margin: 0 0 1rem;
            font-size: 2.2rem;
            letter-spacing: 0.01em;
        }

        .intro p {
            margin: 0;
            color: #e1e1e1;
            font-size: 1.05rem;
            max-width: 680px;
            line-height: 1.6;
        }

        .cards {
            display: grid;
            gap: 1.5rem;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        }

        .card {
            background: rgba(40, 40, 40, 0.85);
            border-radius: 18px;
            padding: 1.75rem;
            border: 1px solid rgba(125, 125, 125, 0.3);
            box-shadow: 0 12px 32px rgba(40, 40, 40, 0.35);
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            transition: transform 0.2s ease, border-color 0.2s ease;
        }

        .card:hover {
            transform: translateY(-4px);
            border-color: rgba(225, 225, 225, 0.4);
        }

        .card h2 {
            margin: 0;
            font-size: 1.25rem;
            color: #e1e1e1;
        }

        .card p {
            margin: 0;
            color: #e1e1e1;
            line-height: 1.5;
        }

        .metrics {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1.25rem;
        }

        .metric-tile {
            background: rgba(70, 70, 70, 0.45);
            border-radius: 16px;
            padding: 1.5rem;
            text-align: center;
            border: 1px solid rgba(225, 225, 225, 0.2);
        }

        .metric-tile h3 {
            margin: 0 0 0.35rem;
            font-size: 0.95rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #e1e1e1;
        }

        .metric-tile span {
            font-size: 2rem;
            font-weight: 700;
        }

        .metric-placeholder {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            font-weight: 500;
            color: #ffffff;
            background: rgba(125, 125, 125, 0.25);
            border-radius: 999px;
            padding: 0.35rem 0.85rem;
        }

        @media (max-width: 768px) {
            main {
                padding: 1.5rem;
            }

            .intro {
                padding: 1.75rem;
            }
        }
    </style>
    <?php nexaHeaderStyles(); ?>
</head>
<body>
    <?php renderNexaHeader(); ?>
    <main>
        <section class="intro">
            <h1>Kontrol Paneline Hoş Geldiniz</h1>
            <p>
                Nexa iş süreçlerini yönetmek için ihtiyaç duyduğunuz tüm verileri tek bir çatı altında sunar.
                Aşağıdaki özetler ürünlerden siparişlere kadar uzanan operasyonlarınızın güncel durumunu gösterir.
            </p>
        </section>

        <section class="metrics">
            <?php foreach ($metricData as $metric) : ?>
                <div class="metric-tile">
                    <h3><?php echo htmlspecialchars($metric['label'], ENT_QUOTES, 'UTF-8'); ?></h3>
                    <?php if ($metric['value'] !== null) : ?>
                        <span><?php echo number_format($metric['value'], 0, ',', '.'); ?></span>
                    <?php else : ?>
                        <span class="metric-placeholder">Veri bekleniyor</span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </section>

        <section class="cards">
            <article class="card" id="urunler">
                <h2>Ürünler</h2>
                <p>Ürün kayıtlarınızı eklediğinizde özetler burada görünecek.</p>
            </article>
            <article class="card" id="fiyatlar">
                <h2>Fiyatlar</h2>
                <p>Fiyatlandırma verileri bağlandığında geçmiş eğilimler ve karşılaştırmalar otomatik olarak listelenecek.</p>
            </article>
            <article class="card" id="tedarikciler">
                <h2>Tedarikçiler</h2>
                <p>Tedarikçi tablonuz hazır olduğunda performans özetleri ve onay durumları burada yer alacak.</p>
            </article>
            <article class="card" id="projeler">
                <h2>Projeler</h2>
                <p>Proje verilerini sisteme aktardığınızda ilerleme ve risk durumu bu bölümde raporlanacak.</p>
            </article>
            <article class="card" id="siparisler">
                <h2>Siparişler</h2>
                <p>Sipariş entegrasyonunu tamamladığınızda gerçek zamanlı akışlar ve SLA durumları bu panelden takip edilecek.</p>
            </article>
        </section>
    </main>
</body>
</html>

