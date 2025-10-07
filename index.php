<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Nexa - Modern İş Yönetimi';
include __DIR__ . '/header.php';
?>
<main class="container py-5">
    <div class="row align-items-center justify-content-between g-5">
        <div class="col-lg-6">
            <h1 class="display-4 fw-bold mb-3">Nexa ile işinizi kolaylaştırın</h1>
            <p class="lead text-muted mb-4">Şirket bilgilerinizi tek noktadan yönetin, ekip arkadaşlarınızla paylaşın ve güncel tutun. Nexa ile süreçleriniz hem daha hızlı hem daha güvenli.</p>
            <div class="d-flex gap-3">
                <a class="btn btn-primary btn-lg" href="/register.php">Hemen Başlayın</a>
                <a class="btn btn-outline-secondary btn-lg" href="/login.php">Zaten hesabım var</a>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h2 class="h4 fw-semibold mb-3">Nexa'yı neden tercih etmelisiniz?</h2>
                    <ul class="list-unstyled small text-muted mb-0">
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Basit ve sezgisel arayüz</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Güvenli kullanıcı yönetimi</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Gerçek zamanlı şirket güncellemeleri</li>
                        <li class="mb-0"><i class="bi bi-check-circle-fill text-success me-2"></i>Esnek API mimarisi</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</main>
</body>
</html>
