<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$user = require_login();

$pdo = get_db_connection();
$companyCountStmt = $pdo->prepare('SELECT COUNT(*) FROM company WHERE user_id = :user_id');
$companyCountStmt->execute([':user_id' => $user['id']]);
$companyCount = (int) $companyCountStmt->fetchColumn();

$pageTitle = 'Panel - Nexa';
$csrfToken = ensure_csrf_token();
$hasSidebar = true;
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
        <main class="col main-content px-3 px-lg-4 py-4">
            <?php if ($hasSidebar): ?>
                <button class="btn btn-outline-secondary d-lg-none mb-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
                    <i class="bi bi-list me-1"></i> Menü
                </button>
            <?php endif; ?>
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between mb-4 gap-3">
                <div>
                    <h1 class="h3 fw-semibold mb-1">Merhaba, <?= e($user['firstname']) ?> 👋</h1>
                    <p class="text-muted mb-0">Kontrol panelinize hoş geldiniz. Şirket bilgilerinizi buradan yönetebilirsiniz.</p>
                </div>
                <a href="company.php" class="btn btn-primary"><i class="bi bi-building me-2"></i>Şirket Detayları</a>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-xl-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h2 class="display-6 fw-semibold mb-0"><?= $companyCount ?></h2>
                                    <p class="text-muted mb-0">Kayıtlı şirket</p>
                                </div>
                                <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                                    <i class="bi bi-buildings fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body">
                            <h6 class="text-muted text-uppercase">Güvenlik</h6>
                            <p class="mb-2">Şifreniz güçlü şifreleme algoritmasıyla korunur.</p>
                            <span class="badge bg-success-subtle text-success"><i class="bi bi-shield-lock me-1"></i>Etkin</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body">
                            <h6 class="text-muted text-uppercase">Başlamak için</h6>
                            <ul class="list-unstyled mb-0 small">
                                <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Şirket bilgilerinizi ekleyin.</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Gerekirse güncelleme yapın.</li>
                                <li class="mb-0"><i class="bi bi-check-circle-fill text-success me-2"></i>Bilgileri ekibinizle paylaşın.</li>
                            </ul>
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
