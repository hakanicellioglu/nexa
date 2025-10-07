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
$currentUser = current_user();
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f5f6fa;
        }
        .sidebar {
            min-height: 100vh;
            background: #111827;
        }
        .sidebar a {
            color: rgba(255, 255, 255, 0.75);
        }
        .sidebar a.active,
        .sidebar a:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg bg-white border-bottom shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="index.php">Nexa</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <?php if ($currentUser === null): ?>
                    <li class="nav-item"><a class="nav-link" href="login.php">Giriş Yap</a></li>
                    <li class="nav-item"><a class="btn btn-primary" href="register.php">Kayıt Ol</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Panel</a></li>
                    <li class="nav-item"><a class="nav-link" href="company.php">Şirket Bilgileri</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i><?= e($currentUser['firstname'] . ' ' . $currentUser['lastname']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><span class="dropdown-item-text text-muted small"><?= e($currentUser['email']) ?></span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Çıkış Yap</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<div class="container">
    <div class="row">
        <?php include __DIR__ . '/sidebar.php'; ?>
        <main class="col-lg-10 ms-auto px-4 py-4">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between mb-4">
                <div>
                    <h1 class="h3 fw-semibold mb-1">Merhaba, <?= e($user['firstname']) ?> 👋</h1>
                    <p class="text-muted mb-0">Kontrol panelinize hoş geldiniz. Şirket bilgilerinizi buradan yönetebilirsiniz.</p>
                </div>
                <a href="company.php" class="btn btn-primary mt-3 mt-lg-0"><i class="bi bi-building me-2"></i>Şirket Detayları</a>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
</body>
</html>
