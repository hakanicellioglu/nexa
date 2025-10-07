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
    .sidebar,
    #sidebarOffcanvas {
        --sidebar-accent: #4f46e5;
        --sidebar-accent-contrast: #ffffff;
        --sidebar-hover: rgba(79, 70, 229, 0.08);
        --sidebar-surface: #ffffff;
        --sidebar-muted: #6b7280;
        --sidebar-text: #1f2937;
        --sidebar-border: #e5e7eb;
        background-color: var(--sidebar-surface);
        color: var(--sidebar-text);
    }

    .sidebar .section-label,
    #sidebarOffcanvas .section-label {
        color: var(--sidebar-muted);
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    .sidebar .nav-link,
    #sidebarOffcanvas .nav-link {
        align-items: center;
        border-radius: 0.5rem;
        color: var(--sidebar-muted);
        display: flex;
        gap: 0.5rem;
        padding: 0.75rem 1rem;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .sidebar .nav-link:hover:not(.active),
    #sidebarOffcanvas .nav-link:hover:not(.active) {
        background-color: var(--sidebar-hover);
        color: var(--sidebar-accent-contrast);
    }

    .sidebar .nav-link.active,
    #sidebarOffcanvas .nav-link.active {
        background-color: var(--sidebar-accent);
        color: var(--sidebar-accent-contrast);
        font-weight: 600;
        box-shadow: 0 0.5rem 1.5rem -1rem rgba(79, 70, 229, 0.6);
    }

    .sidebar .nav-link.active i,
    #sidebarOffcanvas .nav-link.active i,
    .sidebar .nav-link.active span,
    #sidebarOffcanvas .nav-link.active span {
        color: inherit;
    }

    .sidebar .nav .nav-link,
    #sidebarOffcanvas .nav .nav-link {
        padding: 0.65rem 1rem;
    }

    .sidebar .nav .nav .nav-link.active,
    #sidebarOffcanvas .nav .nav .nav-link.active {
        background-color: rgba(79, 70, 229, 0.12);
        color: var(--sidebar-accent);
        font-weight: 600;
        box-shadow: none;
    }

    .sidebar .profile-initials,
    #sidebarOffcanvas .profile-initials {
        background-color: var(--sidebar-accent);
        color: var(--sidebar-accent-contrast);
    }

    .sidebar .logout-btn,
    #sidebarOffcanvas .logout-btn {
        background-color: transparent;
        border-radius: 0.5rem;
        border-color: rgba(79, 70, 229, 0.4);
        color: var(--sidebar-accent);
    }

    .sidebar .logout-btn:hover,
    #sidebarOffcanvas .logout-btn:hover {
        background-color: var(--sidebar-accent);
        border-color: var(--sidebar-accent);
        color: var(--sidebar-accent-contrast);
    }

    .sidebar .border-top,
    #sidebarOffcanvas .border-top {
        border-top-color: var(--sidebar-border) !important;
    }
</style>
<aside class="sidebar col-auto d-none d-lg-flex flex-column flex-shrink-0 align-self-start px-3 py-4 position-sticky top-0 min-vh-100 bg-white border-end" style="width: 260px;">
    <div class="d-flex align-items-center gap-2 mb-5 px-2">
        <i class="bi bi-stars fs-4"></i>
        <div class="fw-bold">Nexa</div>
    </div>
    
    <div class="section-label small mb-2 px-2" style="font-size: 0.7rem;">Menü</div>
    
    <nav class="nav flex-column gap-1 mb-auto">
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
                $linkClasses = 'nav-link px-3 py-2';
                if ($isActive) {
                    $linkClasses .= ' active';
                }
            ?>
            <div>
                <a class="<?= e($linkClasses) ?>" href="<?= e($item['href']) ?>"<?php if ($isActive): ?> aria-current="page"<?php endif; ?>>
                    <i class="bi <?= e($item['icon']) ?>"></i>
                    <span class="small"><?= e($item['label']) ?></span>
                </a>
                <?php if (!empty($childItems)): ?>
                    <div class="nav flex-column gap-1 ps-4 mt-1">
                        <?php foreach ($childItems as $child): ?>
                            <?php
                                $isChildLinkActive = $currentPage === basename($child['href']);
                                $childClasses = 'nav-link px-3 py-2 small';
                                if ($isChildLinkActive) {
                                    $childClasses .= ' active';
                                }
                            ?>
                            <a class="<?= e($childClasses) ?>" href="<?= e($child['href']) ?>"<?php if ($isChildLinkActive): ?> aria-current="page"<?php endif; ?>>
                                <?= e($child['label']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </nav>
    
    <div class="mt-auto pt-3 border-top">
        <div class="d-flex align-items-center gap-2 mb-3 px-2">
            <div class="rounded-circle profile-initials d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-size: 0.85rem;">
                <?= e($initials) ?>
            </div>
            <div class="flex-grow-1 overflow-hidden">
                <div class="fw-medium small text-truncate"><?= e(trim($firstName . ' ' . $lastName)) ?></div>
                <div class="text-secondary small text-truncate" style="font-size: 0.75rem; color: var(--sidebar-muted);">
                    <?= e($email) ?>
                </div>
            </div>
        </div>
        <a class="btn btn-sm w-100 logout-btn" href="logout.php">
            <i class="bi bi-box-arrow-right me-1"></i>Çıkış
        </a>
    </div>
</aside>

<div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel">
    <div class="offcanvas-header border-bottom">
        <div class="fw-bold">Nexa</div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Kapat"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column">
        <div class="section-label small mb-2" style="font-size: 0.7rem;">Menü</div>
        
        <nav class="nav flex-column gap-1 mb-4">
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
                    $linkClasses = 'nav-link px-3 py-2';
                    if ($isActive) {
                        $linkClasses .= ' active';
                    }
                ?>
                <div>
                    <a class="<?= e($linkClasses) ?>" href="<?= e($item['href']) ?>"<?php if ($isActive): ?> aria-current="page"<?php endif; ?>>
                        <i class="bi <?= e($item['icon']) ?>"></i>
                        <span class="small"><?= e($item['label']) ?></span>
                    </a>
                    <?php if (!empty($childItems)): ?>
                        <div class="nav flex-column gap-1 ps-4 mt-1">
                            <?php foreach ($childItems as $child): ?>
                                <?php
                                    $isChildLinkActive = $currentPage === basename($child['href']);
                                    $childClasses = 'nav-link px-3 py-2 small';
                                    if ($isChildLinkActive) {
                                        $childClasses .= ' active';
                                    }
                                ?>
                                <a class="<?= e($childClasses) ?>" href="<?= e($child['href']) ?>"<?php if ($isChildLinkActive): ?> aria-current="page"<?php endif; ?>>
                                    <?= e($child['label']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </nav>
        
        <div class="mt-auto border-top pt-3">
            <div class="d-flex align-items-center gap-2 mb-3">
                <div class="rounded-circle profile-initials d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-size: 0.85rem;">
                    <?= e($initials) ?>
                </div>
                <div class="flex-grow-1 overflow-hidden">
                    <div class="fw-medium small text-truncate"><?= e(trim($firstName . ' ' . $lastName)) ?></div>
                    <div class="text-secondary small text-truncate" style="font-size: 0.75rem; color: var(--sidebar-muted);">
                        <?= e($email) ?>
                    </div>
                </div>
            </div>
            <a class="btn btn-sm w-100 logout-btn" href="logout.php">
                <i class="bi bi-box-arrow-right me-1"></i>Çıkış
            </a>
        </div>
    </div>
</div>
