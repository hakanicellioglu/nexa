<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$user = current_user();

if ($user === null) {
    return;
}

$firstname = $user['firstname'] ?? '';
$lastname = $user['lastname'] ?? '';

if (function_exists('mb_substr')) {
    $firstInitial = mb_substr($firstname, 0, 1, 'UTF-8');
    $lastInitial = mb_substr($lastname, 0, 1, 'UTF-8');
    $initials = mb_strtoupper($firstInitial . $lastInitial, 'UTF-8');
} else {
    $initials = strtoupper(substr($firstname, 0, 1) . substr($lastname, 0, 1));
}

if ($initials === '') {
    $initials = 'N';
}

$currentPage = basename($_SERVER['PHP_SELF']);
$navSections = [
    [
        'title' => 'Genel',
        'items' => [
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
        ],
    ],
];
?>
<style>
    .sidebar .nav-link,
    .offcanvas .nav-link {
        transition: color 0.2s ease, background-color 0.2s ease;
    }

    .sidebar .nav-link.active,
    .sidebar .nav-link:hover,
    .offcanvas .nav-link.active,
    .offcanvas .nav-link:hover {
        transform: none !important;
    }

    .sidebar .profile-avatar {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.85), rgba(118, 75, 162, 0.85));
        padding: 2px;
        box-sizing: border-box;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .sidebar .profile-avatar-inner {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: rgba(15, 23, 42, 0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        letter-spacing: 0.02em;
    }
</style>
<aside class="sidebar col-auto d-none d-lg-flex flex-column flex-shrink-0 align-self-start text-white px-3 py-4 position-sticky top-0 min-vh-100" style="width: 260px;">
    <div class="d-flex align-items-center gap-3 mb-4">
        <div class="rounded-4 bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
            <span>N</span>
        </div>
        <div>
            <div class="fw-semibold">Nexa</div>
            <small class="text-white-50">Yönetim Paneli</small>
        </div>
    </div>
    <?php foreach ($navSections as $section): ?>
        <div class="nav-section mb-4 w-100">
            <div class="text-uppercase text-white-50 fw-semibold small mb-2" style="letter-spacing: .08em;">
                <?= e($section['title']) ?>
            </div>
            <nav class="nav nav-pills flex-column mb-0">
                <?php foreach ($section['items'] as $item): ?>
                    <?php $isActive = $currentPage === basename($item['href']); ?>
                    <a class="nav-link d-flex align-items-center px-3 py-2<?php echo $isActive ? ' active' : ''; ?>" href="<?= e($item['href']) ?>">
                        <i class="bi <?= e($item['icon']) ?> me-2"></i>
                        <span><?= e($item['label']) ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>
    <?php endforeach; ?>
    <div class="mt-auto w-100">
        <div class="p-3 rounded-4 bg-white bg-opacity-10 border border-light border-opacity-10">
            <div class="d-flex align-items-center gap-3">
                <div class="profile-avatar">
                    <div class="profile-avatar-inner text-white">
                        <span><?= e($initials) ?></span>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-semibold text-white mb-1"><?= e($user['firstname'] . ' ' . $user['lastname']) ?></div>
                    <div class="text-white-50 text-truncate small"><?= e($user['email']) ?></div>
                </div>
            </div>
            <a class="btn btn-outline-light btn-sm w-100 mt-3" href="logout.php">
                <i class="bi bi-box-arrow-right me-2"></i>Çıkış Yap
            </a>
        </div>
    </div>
</aside>
<div class="offcanvas offcanvas-start text-bg-dark" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel">
    <div class="offcanvas-header border-bottom border-secondary">
        <h5 class="offcanvas-title" id="sidebarOffcanvasLabel">Nexa</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Kapat"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column">
        <?php foreach ($navSections as $section): ?>
            <div class="nav-section mb-4">
                <div class="text-uppercase text-white-50 fw-semibold small mb-2" style="letter-spacing: .08em;">
                    <?= e($section['title']) ?>
                </div>
                <nav class="nav nav-pills flex-column">
                    <?php foreach ($section['items'] as $item): ?>
                        <?php $isActive = $currentPage === basename($item['href']); ?>
                        <a class="nav-link d-flex align-items-center px-3 py-2<?php echo $isActive ? ' active' : ''; ?>" href="<?= e($item['href']) ?>">
                            <i class="bi <?= e($item['icon']) ?> me-2"></i>
                            <span><?= e($item['label']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </div>
        <?php endforeach; ?>
        <div class="mt-auto border-top border-secondary pt-3 small">
            <div class="fw-semibold text-white mb-1"><?= e($user['firstname'] . ' ' . $user['lastname']) ?></div>
            <div class="text-white-50 text-truncate mb-3"><?= e($user['email']) ?></div>
            <a class="btn btn-outline-light btn-sm w-100" href="logout.php">
                <i class="bi bi-box-arrow-right me-2"></i>Çıkış Yap
            </a>
        </div>
    </div>
</div>
