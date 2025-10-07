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
        'label' => 'Kontrol Paneli',
    ],
    [
        'href' => 'company.php',
        'icon' => 'bi-building',
        'label' => 'Şirket',
        'children' => [
            [
                'href' => 'company_contact.php',
                'label' => 'Şirket Çalışanları',
            ],
        ],
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
<style>
    .sidebar .nav-link,
    #sidebarOffcanvas .nav-link {
        transition: color 0.2s ease, background-color 0.2s ease, border-color 0.2s ease;
    }

    .sidebar .nav-link:hover:not(.active),
    #sidebarOffcanvas .nav-link:hover:not(.active) {
        color: #ffffff !important;
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%) !important;
        border-color: transparent !important;
        box-shadow: 0 0.4rem 1.2rem rgba(99, 102, 241, 0.15);
    }

    .sidebar .nav-link:hover .sidebar-label,
    #sidebarOffcanvas .nav-link:hover .sidebar-label,
    .sidebar .nav-link:hover .sidebar-chevron,
    #sidebarOffcanvas .nav-link:hover .sidebar-chevron,
    .sidebar .nav-link:hover .sidebar-icon,
    #sidebarOffcanvas .nav-link:hover .sidebar-icon {
        color: #ffffff !important;
    }

    .sidebar .nav-link:hover .sidebar-icon-wrapper,
    #sidebarOffcanvas .nav-link:hover .sidebar-icon-wrapper {
        background: rgba(255, 255, 255, 0.24) !important;
    }

    .sidebar .nav .nav-link:hover:not(.active),
    #sidebarOffcanvas .nav .nav-link:hover:not(.active) {
        color: #ffffff !important;
        background: rgba(99, 102, 241, 0.35) !important;
        border-color: transparent !important;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.15);
    }
</style>
<aside class="sidebar col-auto d-none d-lg-flex flex-column flex-shrink-0 align-self-start px-4 py-4 position-sticky top-0 min-vh-100 bg-white bg-opacity-90 border-end border-light-subtle shadow-sm" style="width: 280px; font-size: 0.85rem;">
    <div class="d-flex align-items-center gap-3 mb-5">
        <div class="rounded-4 d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.18) 0%, rgba(139, 92, 246, 0.18) 100%); width: 48px; height: 48px; line-height: 0;">
            <i class="bi bi-stars text-primary fs-4 lh-1"></i>
        </div>
        <div>
            <div class="text-uppercase text-secondary fw-semibold small" id="sidebarOffcanvasLabel">Nexa</div>
        </div>
    </div>
    <div class="text-secondary text-uppercase small fw-semibold mb-3">Menü</div>
    <nav class="nav flex-column gap-2 mb-auto">
        <?php foreach ($navItems as $item): ?>
            <?php
                $childItems = $item['children'] ?? [];
                $isChildActive = false;
                foreach ($childItems as $child) {
                    if ($currentPage === basename($child['href'])) {
                        $isChildActive = true;
                        break;
                    }
                }
                $isActive = $currentPage === basename($item['href']) || $isChildActive;
                $linkClasses = 'nav-link d-flex align-items-center gap-3 px-3 py-2 rounded-4 fw-semibold';
                $linkStyle = '';
                if ($isActive) {
                    $linkClasses .= ' active text-white shadow-sm';
                    $linkStyle = 'background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);';
                } else {
                    $linkClasses .= ' text-body bg-white border border-light-subtle';
                }
                $iconWrapperStyle = $isActive
                    ? 'background: rgba(255, 255, 255, 0.2); color: #ffffff;'
                    : 'background: rgba(99, 102, 241, 0.12); color: #4f46e5;';
                $iconClasses = 'bi ' . $item['icon'] . ' lh-1 fs-5 sidebar-icon';
                $iconClasses .= $isActive ? ' text-white' : ' text-primary';
                $labelClasses = 'fw-semibold small sidebar-label';
                $labelClasses .= $isActive ? ' text-white' : ' text-body';
                $chevronClasses = 'ms-auto sidebar-chevron';
                $chevronClasses .= $isActive ? ' text-white-75' : ' text-primary';
            ?>
            <div>
                <a class="<?= e($linkClasses) ?>" href="<?= e($item['href']) ?>"<?php if ($isActive): ?> aria-current="page"<?php endif; ?><?php if ($linkStyle !== ''): ?> style="<?= e($linkStyle) ?>"<?php endif; ?>>
                    <span class="sidebar-icon-wrapper d-inline-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="<?= e($iconWrapperStyle) ?> width: 42px; height: 42px; line-height: 0;">
                        <i class="<?= e($iconClasses) ?>"></i>
                    </span>
                    <span class="<?= e($labelClasses) ?>"><?= e($item['label']) ?></span>
                    <?php if ($isActive): ?>
                        <span class="<?= e($chevronClasses) ?>"><i class="bi bi-chevron-right lh-1"></i></span>
                    <?php endif; ?>
                </a>
                <?php if (!empty($childItems)): ?>
                    <div class="nav flex-column gap-1 ps-5 mt-1">
                        <?php foreach ($childItems as $child): ?>
                            <?php
                                $isChildLinkActive = $currentPage === basename($child['href']);
                                $childClasses = 'nav-link px-3 py-2 rounded-4 small fw-medium sidebar-label';
                                $childStyle = '';
                                if ($isChildLinkActive) {
                                    $childClasses .= ' active text-primary-emphasis shadow-sm';
                                    $childStyle = 'background: rgba(99, 102, 241, 0.18);';
                                } else {
                                    $childClasses .= ' text-body-secondary';
                                }
                            ?>
                            <a class="<?= e($childClasses) ?>" href="<?= e($child['href']) ?>"<?php if ($isChildLinkActive): ?> aria-current="page"<?php endif; ?><?php if ($childStyle !== ''): ?> style="<?= e($childStyle) ?>"<?php endif; ?>>
                                <?= e($child['label']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </nav>
    <div class="mt-auto pt-4 border-top border-light-subtle">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; line-height: 0; color: #4f46e5;">
                <span class="fw-semibold" style="line-height: 1;">
                    <?= e($initials) ?>
                </span>
            </div>
            <div class="text-body">
                <div class="fw-semibold mb-1"><?= e(trim($firstName . ' ' . $lastName)) ?></div>
                <div class="text-body-secondary small text-truncate" style="max-width: 150px;">
                    <?= e($email) ?>
                </div>
            </div>
        </div>
        <a class="btn btn-outline-primary btn-sm w-100 mt-4" href="logout.php">
            <i class="bi bi-box-arrow-right lh-1 me-2"></i>Çıkış Yap
        </a>
    </div>
</aside>
<div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel" style="font-size: 0.9rem; background: rgba(255, 255, 255, 0.95);">
    <div class="offcanvas-header border-bottom border-light-subtle">
        <div>
            <div class="text-uppercase text-secondary fw-semibold small">Nexa</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Kapat"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column">
        <div class="text-secondary text-uppercase small fw-semibold mb-3">Menü</div>
        <nav class="nav flex-column gap-2 mb-4">
            <?php foreach ($navItems as $item): ?>
                <?php
                    $childItems = $item['children'] ?? [];
                    $isChildActive = false;
                    foreach ($childItems as $child) {
                        if ($currentPage === basename($child['href'])) {
                            $isChildActive = true;
                            break;
                        }
                    }
                    $isActive = $currentPage === basename($item['href']) || $isChildActive;
                    $linkClasses = 'nav-link d-flex align-items-center gap-3 px-3 py-2 rounded-4 fw-semibold';
                    $linkStyle = '';
                    if ($isActive) {
                        $linkClasses .= ' active text-white shadow-sm';
                        $linkStyle = 'background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);';
                    } else {
                        $linkClasses .= ' text-body bg-white border border-light-subtle';
                    }
                    $iconWrapperStyle = $isActive
                        ? 'background: rgba(255, 255, 255, 0.2); color: #ffffff;'
                        : 'background: rgba(99, 102, 241, 0.12); color: #4f46e5;';
                    $iconClasses = 'bi ' . $item['icon'] . ' lh-1 fs-5 sidebar-icon';
                    $iconClasses .= $isActive ? ' text-white' : ' text-primary';
                    $labelClasses = 'fw-semibold small sidebar-label';
                    $labelClasses .= $isActive ? ' text-white' : ' text-body';
                    $chevronClasses = 'ms-auto sidebar-chevron';
                    $chevronClasses .= $isActive ? ' text-white-75' : ' text-primary';
                ?>
                <div>
                    <a class="<?= e($linkClasses) ?>" href="<?= e($item['href']) ?>"<?php if ($isActive): ?> aria-current="page"<?php endif; ?><?php if ($linkStyle !== ''): ?> style="<?= e($linkStyle) ?>"<?php endif; ?>>
                        <span class="sidebar-icon-wrapper d-inline-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="<?= e($iconWrapperStyle) ?> width: 42px; height: 42px; line-height: 0;">
                            <i class="<?= e($iconClasses) ?>"></i>
                        </span>
                        <span class="<?= e($labelClasses) ?>"><?= e($item['label']) ?></span>
                        <?php if ($isActive): ?>
                            <span class="<?= e($chevronClasses) ?>"><i class="bi bi-chevron-right lh-1"></i></span>
                        <?php endif; ?>
                    </a>
                    <?php if (!empty($childItems)): ?>
                        <div class="nav flex-column gap-1 ps-5 mt-1">
                            <?php foreach ($childItems as $child): ?>
                                <?php
                                    $isChildLinkActive = $currentPage === basename($child['href']);
                                $childClasses = 'nav-link px-3 py-2 rounded-4 small fw-medium sidebar-label';
                                $childStyle = '';
                                if ($isChildLinkActive) {
                                    $childClasses .= ' active text-primary-emphasis shadow-sm';
                                    $childStyle = 'background: rgba(99, 102, 241, 0.18);';
                                } else {
                                        $childClasses .= ' text-body-secondary';
                                    }
                                ?>
                                <a class="<?= e($childClasses) ?>" href="<?= e($child['href']) ?>"<?php if ($isChildLinkActive): ?> aria-current="page"<?php endif; ?><?php if ($childStyle !== ''): ?> style="<?= e($childStyle) ?>"<?php endif; ?>>
                                    <?= e($child['label']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </nav>
        <div class="mt-auto border-top border-light-subtle pt-3">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; line-height: 0; color: #4f46e5;">
                    <span class="fw-semibold" style="line-height: 1;">
                        <?= e($initials) ?>
                    </span>
                </div>
                <div>
                    <div class="fw-semibold text-body mb-1"><?= e(trim($firstName . ' ' . $lastName)) ?></div>
                    <div class="text-body-secondary small text-truncate" style="max-width: 180px;">
                        <?= e($email) ?>
                    </div>
                </div>
            </div>
            <a class="btn btn-outline-primary btn-sm w-100" href="logout.php">
                <i class="bi bi-box-arrow-right lh-1 me-2"></i>Çıkış Yap
            </a>
        </div>
    </div>
</div>
