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
    [
        'href' => 'company_contact.php',
        'icon' => 'bi-people',
        'label' => 'İletişim Kişileri',
    ],
];

$firstName = (string)($user['firstname'] ?? '');
$lastName = (string)($user['lastname'] ?? '');
$email = (string)($user['email'] ?? '');

$initials = '';
if ($firstName !== '' || $lastName !== '') {
    if (function_exists('mb_substr')) {
        $initials .= $firstName !== '' ? mb_substr($firstName, 0, 1) : '';
        $initials .= $lastName !== '' ? mb_substr($lastName, 0, 1) : '';
    } else {
        $initials .= $firstName !== '' ? substr($firstName, 0, 1) : '';
        $initials .= $lastName !== '' ? substr($lastName, 0, 1) : '';
    }
} elseif ($email !== '') {
    if (function_exists('mb_substr')) {
        $initials = mb_substr($email, 0, 1);
    } else {
        $initials = substr($email, 0, 1);
    }
}

if (function_exists('mb_strtoupper')) {
    $initials = mb_strtoupper($initials, 'UTF-8');
} else {
    $initials = strtoupper($initials);
}
?>
<aside class="sidebar col-auto d-none d-lg-flex flex-column flex-shrink-0 align-self-start px-4 py-4 position-sticky top-0 min-vh-100" style="width: 280px;">
    <div class="d-flex align-items-center gap-3 mb-5">
        <div class="rounded-4 d-flex align-items-center justify-content-center" style="background: rgba(255, 255, 255, 0.08); width: 48px; height: 48px;">
            <i class="bi bi-stars text-white fs-4"></i>
        </div>
        <div>
            <div class="text-uppercase text-white-50 fw-semibold small">Nexa</div>
            <div class="text-white fw-bold fs-5">Kontrol Paneli</div>
        </div>
    </div>
    <div class="text-white-50 text-uppercase small fw-semibold mb-3">Menü</div>
    <nav class="nav flex-column gap-2 mb-auto">
        <?php foreach ($navItems as $item): ?>
            <?php
                $isActive = $currentPage === basename($item['href']);
                $linkClasses = 'nav-link d-flex align-items-center gap-3 px-3 py-3 rounded-4';
                if ($isActive) {
                    $linkClasses .= ' active';
                }
            ?>
            <a class="<?= e($linkClasses) ?>" href="<?= e($item['href']) ?>"<?php if ($isActive): ?> aria-current="page"<?php endif; ?>>
                <span class="d-inline-flex align-items-center justify-content-center rounded-3" style="background: rgba(255, 255, 255, 0.08); width: 42px; height: 42px;">
                    <i class="bi <?= e($item['icon']) ?> text-white<?= $isActive ? '' : ' opacity-75' ?>"></i>
                </span>
                <span class="fw-medium text-white<?= $isActive ? '' : ' opacity-75' ?>"><?= e($item['label']) ?></span>
                <?php if ($isActive): ?>
                    <span class="ms-auto text-white-50"><i class="bi bi-chevron-right"></i></span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </nav>
    <div class="mt-auto pt-4 border-top border-light border-opacity-25">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-white bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                <span class="fw-semibold text-white"><?= e($initials) ?></span>
            </div>
            <div class="text-white">
                <div class="fw-semibold mb-1"><?= e(trim($firstName . ' ' . $lastName)) ?></div>
                <div class="text-white-50 small text-truncate" style="max-width: 150px;"><?= e($email) ?></div>
            </div>
        </div>
        <a class="btn btn-outline-light btn-sm w-100 mt-4" href="logout.php">
            <i class="bi bi-box-arrow-right me-2"></i>Çıkış Yap
        </a>
    </div>
</aside>
<div class="offcanvas offcanvas-start text-bg-dark" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel">
    <div class="offcanvas-header border-bottom border-secondary">
        <div>
            <div class="text-uppercase text-white-50 fw-semibold small">Nexa</div>
            <h5 class="offcanvas-title text-white mb-0" id="sidebarOffcanvasLabel">Kontrol Paneli</h5>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Kapat"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column">
        <div class="text-white-50 text-uppercase small fw-semibold mb-3">Menü</div>
        <nav class="nav flex-column gap-2 mb-4">
            <?php foreach ($navItems as $item): ?>
                <?php
                    $isActive = $currentPage === basename($item['href']);
                    $linkClasses = 'nav-link d-flex align-items-center gap-3 px-3 py-3 rounded-4';
                    if ($isActive) {
                        $linkClasses .= ' active';
                    }
                ?>
                <a class="<?= e($linkClasses) ?>" href="<?= e($item['href']) ?>"<?php if ($isActive): ?> aria-current="page"<?php endif; ?>>
                    <span class="d-inline-flex align-items-center justify-content-center rounded-3" style="background: rgba(255, 255, 255, 0.08); width: 42px; height: 42px;">
                        <i class="bi <?= e($item['icon']) ?> text-white<?= $isActive ? '' : ' opacity-75' ?>"></i>
                    </span>
                    <span class="fw-medium text-white<?= $isActive ? '' : ' opacity-75' ?>"><?= e($item['label']) ?></span>
                    <?php if ($isActive): ?>
                        <span class="ms-auto text-white-50"><i class="bi bi-chevron-right"></i></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="mt-auto border-top border-secondary pt-3">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="rounded-circle bg-white bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <span class="fw-semibold text-white"><?= e($initials) ?></span>
                </div>
                <div>
                    <div class="fw-semibold text-white mb-1"><?= e(trim($firstName . ' ' . $lastName)) ?></div>
                    <div class="text-white-50 small text-truncate" style="max-width: 180px;"><?= e($email) ?></div>
                </div>
            </div>
            <a class="btn btn-outline-light btn-sm w-100" href="logout.php">
                <i class="bi bi-box-arrow-right me-2"></i>Çıkış Yap
            </a>
        </div>
    </div>
</div>
