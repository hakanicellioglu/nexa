<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/config.php';

$userFullName = trim(($_SESSION['firstname'] ?? '') . ' ' . ($_SESSION['lastname'] ?? ''));

/**
 * @param PDO   $pdo
 * @param string $sql
 * @param array<int|string, mixed> $params
 */
function fetchCount(PDO $pdo, string $sql, array $params = []): ?int
{
    try {
        $statement = $pdo->prepare($sql);
        $statement->execute($params);

        $value = $statement->fetchColumn();

        return $value !== false ? (int) $value : null;
    } catch (Throwable $exception) {
        error_log('Dashboard metric query failed: ' . $exception->getMessage());

        return null;
    }
}

$dashboardMetrics = [
    [
        'label' => 'Toplam Proje',
        'value' => fetchCount(
            $pdo,
            'SELECT COUNT(*) FROM projects'
        ),
        'description' => 'Kayıtlı proje sayısı',
    ],
    [
        'label' => 'Aylık Siparişler',
        'value' => fetchCount(
            $pdo,
            'SELECT COUNT(*) FROM orders WHERE YEAR(order_date) = YEAR(CURRENT_DATE()) AND MONTH(order_date) = MONTH(CURRENT_DATE())'
        ),
        'description' => 'Bu ay oluşturulan siparişler',
    ],
    [
        'label' => 'Aktif Tedarikçiler',
        'value' => fetchCount(
            $pdo,
            'SELECT COUNT(*) FROM suppliers'
        ),
        'description' => 'Kayıtlı tedarikçi sayısı',
    ],
];

$quickActions = [
    ['label' => 'Yeni Sipariş', 'href' => '#'],
    ['label' => 'Proje Oluştur', 'href' => '#'],
    ['label' => 'Fiyat Listesi', 'href' => '#'],
    ['label' => 'Tedarikçi Ekle', 'href' => '#'],
];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontrol Paneli</title>
    <?php include __DIR__ . '/fonts/monoton.php'; ?>
    <?php include __DIR__ . '/cdn/bootstrap.php'; ?>
    <style>
        body {
            background-color: #f1f3f5;
            font-family: 'Inter', sans-serif;
        }

        .dashboard-wrapper {
            display: grid;
            grid-template-columns: 280px 1fr;
            min-height: 100vh;
        }

        .content-area {
            padding: 2rem;
        }

        @media (max-width: 992px) {
            .dashboard-wrapper {
                grid-template-columns: 1fr;
            }

            .content-area {
                padding: 1.5rem 1rem 3rem;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include __DIR__ . '/sidebar.php'; ?>
        <main class="content-area">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <div>
                    <h1 class="h3 fw-bold">Merhaba, <?= htmlspecialchars($userFullName !== '' ? $userFullName : ($_SESSION['username'] ?? 'Kullanıcı'), ENT_QUOTES, 'UTF-8') ?>!</h1>
                    <p class="text-muted mb-0">Bugünkü özetinizi aşağıda bulabilirsiniz.</p>
                </div>
                <a href="logout.php" class="btn btn-outline-danger">Çıkış yap</a>
            </div>
            <div class="row g-4">
                <?php foreach ($dashboardMetrics as $metric): ?>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h2 class="h6 text-uppercase text-muted"><?= htmlspecialchars($metric['label'], ENT_QUOTES, 'UTF-8'); ?></h2>
                                <p class="display-6 fw-bold mb-0">
                                    <?= $metric['value'] !== null ? number_format($metric['value'], 0, ',', '.') : '—'; ?>
                                </p>
                                <small class="text-muted"><?= htmlspecialchars($metric['description'], ENT_QUOTES, 'UTF-8'); ?></small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body">
                    <h2 class="h5 fw-bold">Hızlı Erişim</h2>
                    <p class="text-muted">Sık kullanılan araçlarınıza buradan ulaşabilirsiniz.</p>
                    <div class="row g-3">
                        <?php foreach ($quickActions as $action): ?>
                            <div class="col-sm-6 col-lg-3">
                                <a href="<?= htmlspecialchars($action['href'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light w-100 py-3 border rounded-3 shadow-sm">
                                    <?= htmlspecialchars($action['label'], ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
