<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Nexa - Modern İş Yönetimi';
$csrfToken = ensure_csrf_token();
$user = current_user();
$hasSidebar = $user !== null;
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= e($csrfToken) ?>">
    <title><?= e($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f5f6fa;
        }
        .app-layout {
            min-height: 100vh;
        }
        .sidebar {
            background: #111827;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.75);
            font-weight: 500;
        }
        .sidebar .nav-link.active,
        .sidebar .nav-link:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.12);
        }
        .sidebar .nav-link i {
            width: 1.5rem;
        }
        .main-content {
            min-height: 100vh;
        }
    </style>
</head>
<body>
<div class="container-fluid app-layout">
    <div class="row flex-nowrap">
        <?php include __DIR__ . '/sidebar.php'; ?>
        <main class="<?= $hasSidebar ? 'col main-content px-3 px-lg-4 py-4' : 'col-12 main-content px-3 px-md-5 py-5'; ?>">
            <?php if ($hasSidebar): ?>
                <button class="btn btn-outline-secondary d-lg-none mb-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
                    <i class="bi bi-list me-1"></i> Menü
                </button>
            <?php endif; ?>
            <div class="container-xl px-0">
                <div class="row align-items-center justify-content-between g-5 py-4">
                    <div class="col-xl-6">
                        <h1 class="display-5 fw-bold mb-3">Nexa ile cam siparişlerinizi yönetin</h1>
                        <p class="lead text-muted mb-4">Cam siparişlerinin durumunu takip edin, fiyat değişimlerini anında görün ve tüm müşteri taleplerini tek bir panelde yönetin. Nexa ile cam işlerinizi daha şeffaf ve kontrollü hale getirin.</p>
                        <div class="d-flex flex-wrap gap-3">
                            <a class="btn btn-primary btn-lg" href="register.php">Hemen Başlayın</a>
                            <a class="btn btn-outline-secondary btn-lg" href="login.php">Zaten hesabım var</a>
                        </div>
                    </div>
                    <div class="col-xl-5">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4">
                                <h2 class="h4 fw-semibold mb-3">Nexa'yı cam sektöründe neden tercih etmelisiniz?</h2>
                                <ul class="list-unstyled small text-muted mb-0">
                                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Basit ve sezgisel cam siparişi arayüzü</li>
                                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Güvenli müşteri ve proje yönetimi</li>
                                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Gerçek zamanlı fiyat güncellemeleri</li>
                                    <li class="mb-0"><i class="bi bi-check-circle-fill text-success me-2"></i>Esnek raporlama ve API entegrasyonu</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
