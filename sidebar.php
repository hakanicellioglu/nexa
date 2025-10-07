<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$user = current_user();

if ($user === null) {
    return;
}

$currentPage = basename($_SERVER['PHP_SELF']);
$navItems = [
    [
        'href' => 'dashboard.php',
        'icon' => 'bi-speedometer',
        'label' => 'Özet',
    ],
    [
        'href' => 'company.php',
        'icon' => 'bi-building',
        'label' => 'Şirket',
    ],
];
?>
<aside class="sidebar d-none d-lg-flex flex-column flex-shrink-0 align-self-start text-white px-3 py-4 position-sticky top-0 min-vh-100" style="width: 260px;">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <span class="fs-5 fw-semibold">Nexa</span>
        <span class="badge bg-primary-subtle text-primary">Panel</span>
    </div>
    <nav class="nav nav-pills flex-column gap-1 mb-auto">
        <?php foreach ($navItems as $item): ?>
            <?php $isActive = $currentPage === basename($item['href']); ?>
            <a class="nav-link d-flex align-items-center px-3 py-2 rounded-3<?php echo $isActive ? ' active' : ''; ?>" href="<?= e($item['href']) ?>">
                <i class="bi <?= e($item['icon']) ?> me-2"></i>
                <span><?= e($item['label']) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
    <div class="mt-auto pt-4 border-top border-light-subtle small">
        <div class="fw-semibold text-white mb-1"><?= e($user['firstname'] . ' ' . $user['lastname']) ?></div>
        <div class="text-white-50 text-truncate"><?= e($user['email']) ?></div>
        <a class="btn btn-outline-light btn-sm w-100 mt-3" href="logout.php">
            <i class="bi bi-box-arrow-right me-2"></i>Çıkış Yap
        </a>
    </div>
</aside>
<div class="offcanvas offcanvas-start text-bg-dark" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel">
    <div class="offcanvas-header border-bottom border-secondary">
        <h5 class="offcanvas-title" id="sidebarOffcanvasLabel">Nexa</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Kapat"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column">
        <nav class="nav nav-pills flex-column gap-1 mb-4">
            <?php foreach ($navItems as $item): ?>
                <?php $isActive = $currentPage === basename($item['href']); ?>
                <a class="nav-link d-flex align-items-center px-3 py-2 rounded-3<?php echo $isActive ? ' active' : ''; ?>" href="<?= e($item['href']) ?>">
                    <i class="bi <?= e($item['icon']) ?> me-2"></i>
                    <span><?= e($item['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="mt-auto border-top border-secondary pt-3 small">
            <div class="fw-semibold text-white mb-1"><?= e($user['firstname'] . ' ' . $user['lastname']) ?></div>
            <div class="text-white-50 text-truncate mb-3"><?= e($user['email']) ?></div>
            <a class="btn btn-outline-light btn-sm w-100" href="logout.php">
                <i class="bi bi-box-arrow-right me-2"></i>Çıkış Yap
            </a>
        </div>
    </div>
</div>
