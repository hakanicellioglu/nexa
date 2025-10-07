<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$user = require_login();

$pdo = get_db_connection();
$companyCountStmt = $pdo->prepare('SELECT COUNT(*) FROM company WHERE user_id = :user_id');
$companyCountStmt->execute([':user_id' => $user['id']]);
$companyCount = (int) $companyCountStmt->fetchColumn();

$pageTitle = 'Panel - Nexa';
include __DIR__ . '/header.php';
?>
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
</body>
</html>
