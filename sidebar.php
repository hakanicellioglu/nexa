<?php
declare(strict_types=1);

// Sidebar component for Nexa platform

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/**
 * @psalm-return array{sections: list<array<string, mixed>>, user: array<string, string>, currentPage: string}
 */
$sidebarState = static function (): array {
    $currentPage = basename($_SERVER['PHP_SELF'] ?? '') ?: 'index.php';

    $user = (array)($_SESSION['user'] ?? []);

    $firstName = trim((string)($user['firstname'] ?? ''));
    $lastName = trim((string)($user['lastname'] ?? ''));
    $fullName = trim($firstName . ' ' . $lastName);
    if ($fullName === '') {
        $fullName = 'Misafir Kullanıcı';
    }

    $username = trim((string)($user['username'] ?? ''));
    $email = trim((string)($user['email'] ?? ''));

    $avatarSource = $fullName !== 'Misafir Kullanıcı' ? $fullName : ($username !== '' ? $username : $email);
    $avatarInitials = 'NK';
    if ($avatarSource !== '') {
        $words = preg_split('/\s+/u', $avatarSource) ?: [];
        $initialLetters = array_map(static function (string $part): string {
            return mb_strtoupper(mb_substr($part, 0, 1, 'UTF-8'), 'UTF-8');
        }, array_filter($words));

        if ($initialLetters !== []) {
            $avatarInitials = implode('', array_slice($initialLetters, 0, 2));
        }
    }

    $userDisplay = [
        'fullName' => $fullName,
        'username' => $username,
        'email' => $email,
        'avatar' => $avatarInitials,
    ];

    $sections = [
        [
            'id' => 'nav-general',
            'title' => 'Genel',
            'items' => [
                [
                    'label' => 'Kontrol Paneli',
                    'href' => 'dashboard.php',
                    'match' => ['dashboard.php', 'index.php'],
                ],
                [
                    'label' => 'Ürünler',
                    'href' => '#urunler',
                ],
                [
                    'label' => 'Tedarikçiler',
                    'href' => '#tedarikciler',
                ],
            ],
        ],
        [
            'id' => 'nav-operations',
            'title' => 'Operasyon',
            'items' => [
                [
                    'label' => 'Fiyatlar',
                    'href' => '#fiyatlar',
                ],
                [
                    'label' => 'Projeler',
                    'type' => 'submenu',
                    'children' => [
                        ['label' => 'Aktif Projeler', 'href' => '#projeler-aktif'],
                        ['label' => 'Bekleyenler', 'href' => '#projeler-bekleyenler'],
                        ['label' => 'Tamamlananlar', 'href' => '#projeler-tamamlananlar'],
                    ],
                ],
                [
                    'label' => 'Siparişler',
                    'href' => '#siparisler',
                ],
            ],
        ],
    ];

    return [
        'sections' => $sections,
        'user' => $userDisplay,
        'currentPage' => $currentPage,
    ];
};

$sidebarData = $sidebarState();

$escape = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexa - Menü</title>
    <?php include __DIR__ . '/fonts/monoton.php'; ?>
    <style>
        <?php include __DIR__ . '/assets/css/root.css'; ?>

        body {
            margin: 0;
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background-color: var(--background-primary);
            color: var(--text-primary);
            display: flex;
            min-height: 100vh;
            position: relative;
        }

        body.sidebar-collapsed .sidebar {
            transform: translateX(-100%);
            opacity: 0;
            pointer-events: none;
        }

        body.sidebar-collapsed .sidebar-toggle {
            left: var(--spacing-lg);
        }

        .sidebar {
            width: min(320px, 80vw);
            background-color: var(--surface-primary);
            border-right: 1px solid var(--border-secondary);
            display: flex;
            flex-direction: column;
            padding: var(--spacing-xl);
            gap: var(--spacing-xl);
            box-shadow: var(--shadow-md);
            transition: transform var(--transition-fast), opacity var(--transition-fast);
        }

        .sidebar-toggle {
            position: fixed;
            top: var(--spacing-lg);
            left: calc(min(320px, 80vw) + var(--spacing-lg));
            z-index: 100;
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-xs);
            padding: var(--spacing-sm) var(--spacing-md);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-secondary);
            background-color: var(--surface-primary);
            color: inherit;
            cursor: pointer;
            font: inherit;
            box-shadow: var(--shadow-sm);
            transition: background-color var(--transition-fast), transform var(--transition-fast);
        }

        .sidebar-toggle:hover,
        .sidebar-toggle:focus-visible {
            background-color: var(--surface-secondary);
            outline: none;
            transform: translateY(-1px);
        }

        .sidebar-toggle-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            line-height: 1;
            min-width: 1.5rem;
        }

        .sidebar-toggle-icon .icon-close {
            display: none;
        }

        body.sidebar-collapsed .sidebar-toggle-icon .icon-open {
            display: none;
        }

        body.sidebar-collapsed .sidebar-toggle-icon .icon-close {
            display: inline;
        }

        .visually-hidden {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
        }

        .brand-logo {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--color-secondary) 0%, var(--color-primary) 100%);
            display: grid;
            place-items: center;
            color: var(--text-inverse);
            font-family: 'Monoton', cursive;
            font-size: var(--font-size-lg);
            letter-spacing: 1px;
        }

        .brand-name {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-xs);
        }

        .brand-name span:first-child {
            font-family: 'Monoton', cursive;
            font-size: var(--font-size-2xl);
            color: var(--color-secondary);
            letter-spacing: 1px;
        }

        .brand-name span:last-child {
            font-size: var(--font-size-sm);
            color: var(--text-secondary);
        }

        nav {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-lg);
        }

        .nav-group {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-sm);
        }

        .nav-group-title {
            font-size: var(--font-size-xs);
            font-weight: var(--font-weight-semibold);
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-tertiary);
        }

        .nav-list {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-xs);
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .nav-item > a,
        .nav-item > button {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            padding: var(--spacing-sm) var(--spacing-md);
            border: none;
            border-radius: var(--radius-lg);
            background: transparent;
            color: inherit;
            text-decoration: none;
            font: inherit;
            cursor: pointer;
            transition: background-color var(--transition-fast), transform var(--transition-fast);
        }

        .nav-item > a:hover,
        .nav-item > button:hover,
        .nav-item > a:focus-visible,
        .nav-item > button:focus-visible {
            background-color: var(--surface-secondary);
            outline: none;
            transform: translateX(2px);
        }

        .nav-item > a[aria-current="page"],
        .nav-item > button[aria-expanded="true"] {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.12), rgba(14, 165, 233, 0.12));
            color: var(--color-secondary-dark);
        }

        details.nav-submenu {
            border-radius: var(--radius-lg);
            background-color: transparent;
        }

        details.nav-submenu > summary {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: var(--spacing-sm) var(--spacing-md);
            cursor: pointer;
            list-style: none;
            border-radius: var(--radius-lg);
            transition: background-color var(--transition-fast), transform var(--transition-fast);
        }

        details.nav-submenu > summary::-webkit-details-marker {
            display: none;
        }

        details.nav-submenu[open] > summary,
        details.nav-submenu > summary:focus-visible,
        details.nav-submenu > summary:hover {
            background-color: var(--surface-secondary);
            outline: none;
            transform: translateX(2px);
        }

        .submenu-list {
            margin: 0;
            padding: 0 var(--spacing-md) var(--spacing-sm) var(--spacing-lg);
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: var(--spacing-xs);
        }

        .submenu-list a {
            display: block;
            padding: var(--spacing-xs) var(--spacing-sm);
            border-radius: var(--radius-md);
            text-decoration: none;
            color: var(--text-secondary);
            transition: color var(--transition-fast), background-color var(--transition-fast);
        }

        .submenu-list a:hover,
        .submenu-list a:focus-visible {
            color: var(--text-primary);
            background-color: rgba(99, 102, 241, 0.12);
            outline: none;
        }

        .profile {
            margin-top: auto;
            padding-top: var(--spacing-lg);
            border-top: 1px solid var(--border-secondary);
            display: flex;
            flex-direction: column;
            gap: var(--spacing-md);
        }

        .profile-info {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
        }

        .avatar {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.18), rgba(14, 165, 233, 0.18));
            display: grid;
            place-items: center;
            font-weight: var(--font-weight-semibold);
            color: var(--color-secondary-dark);
        }

        .profile-text {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .profile-text .name {
            font-weight: var(--font-weight-semibold);
        }

        .profile-text .username {
            font-size: var(--font-size-sm);
            color: var(--text-secondary);
        }

        .profile-actions {
            position: relative;
        }

        .dropdown-toggle {
            width: 100%;
            background-color: var(--surface-tertiary);
            border: 1px solid var(--border-secondary);
            border-radius: var(--radius-lg);
            padding: var(--spacing-sm) var(--spacing-md);
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            font: inherit;
            color: inherit;
        }

        .dropdown-menu {
            position: absolute;
            right: 0;
            bottom: calc(100% + var(--spacing-xs));
            background-color: var(--surface-primary);
            border: 1px solid var(--border-secondary);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-lg);
            min-width: 180px;
            display: none;
            flex-direction: column;
            padding: var(--spacing-xs) 0;
            z-index: 10;
        }

        .dropdown-menu a {
            padding: var(--spacing-sm) var(--spacing-lg);
            text-decoration: none;
            color: var(--text-primary);
            transition: background-color var(--transition-fast);
        }

        .dropdown-menu a:hover,
        .dropdown-menu a:focus-visible {
            background-color: var(--surface-secondary);
            outline: none;
        }

        .profile-actions[data-open="true"] .dropdown-menu {
            display: flex;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: min(280px, 100vw);
                padding: var(--spacing-lg);
            }

            .brand-name span:first-child {
                font-size: var(--font-size-xl);
            }
        }
    </style>
</head>
<body>
    <button type="button" class="sidebar-toggle" aria-expanded="true" aria-controls="nexa-sidebar" aria-label="Menüyü Gizle">
        <span class="sidebar-toggle-icon" aria-hidden="true">
            <span class="icon-open" aria-hidden="true">☰</span>
            <span class="icon-close" aria-hidden="true">✕</span>
        </span>
        <span class="visually-hidden sidebar-toggle-text">Menüyü Gizle</span>
    </button>

    <aside id="nexa-sidebar" class="sidebar" aria-label="Ana menü">
        <header class="brand">
            <div class="brand-logo" aria-hidden="true">N</div>
            <div class="brand-name">
                <span>Nexa</span>
                <span>Yönetim Paneli</span>
            </div>
        </header>

        <nav>
            <?php foreach ($sidebarData['sections'] as $section): ?>
                <section class="nav-group" aria-labelledby="<?= $escape((string)$section['id']); ?>">
                    <h2 id="<?= $escape((string)$section['id']); ?>" class="nav-group-title">
                        <?= $escape((string)$section['title']); ?>
                    </h2>
                    <ul class="nav-list">
                        <?php foreach ($section['items'] as $item): ?>
                            <?php
                                $type = (string)($item['type'] ?? 'link');
                                if ($type === 'submenu') {
                                    $children = (array)($item['children'] ?? []);
                                    $isCurrent = array_reduce(
                                        $children,
                                        function (bool $carry, array $child) use ($sidebarData): bool {
                                            $href = (string)($child['href'] ?? '');
                                            $match = (array)($child['match'] ?? []);
                                            if ($match === [] && $href !== '') {
                                                $match = [$href];
                                            }

                                            return $carry || in_array($sidebarData['currentPage'], array_map('basename', $match), true);
                                        },
                                        false
                                    );
                            ?>
                                <li class="nav-item">
                                    <details class="nav-submenu" <?= $isCurrent ? 'open' : ''; ?>>
                                        <summary aria-expanded="<?= $isCurrent ? 'true' : 'false'; ?>">
                                            <?= $escape((string)$item['label']); ?>
                                            <span aria-hidden="true">▾</span>
                                        </summary>
                                        <ul class="submenu-list">
                                            <?php foreach ($children as $child): ?>
                                                <?php
                                                    $childHref = (string)($child['href'] ?? '#');
                                                    $childMatch = (array)($child['match'] ?? []);
                                                    if ($childMatch === [] && $childHref !== '') {
                                                        $childMatch = [$childHref];
                                                    }

                                                    $isChildCurrent = in_array($sidebarData['currentPage'], array_map('basename', $childMatch), true);
                                                ?>
                                                <li>
                                                    <a href="<?= $escape($childHref); ?>" <?= $isChildCurrent ? 'aria-current="page"' : ''; ?>>
                                                        <?= $escape((string)$child['label']); ?>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </details>
                                </li>
                            <?php
                                continue;
                                }

                                $href = (string)($item['href'] ?? '#');
                                $match = (array)($item['match'] ?? []);
                                if ($match === [] && $href !== '') {
                                    $match = [$href];
                                }

                                $isCurrent = in_array($sidebarData['currentPage'], array_map('basename', $match), true);
                            ?>
                                <li class="nav-item">
                                    <a href="<?= $escape($href); ?>" <?= $isCurrent ? 'aria-current="page"' : ''; ?>>
                                        <?= $escape((string)$item['label']); ?>
                                    </a>
                                </li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endforeach; ?>
        </nav>

        <section class="profile" aria-label="Profil">
            <div class="profile-info">
                <div class="avatar" aria-hidden="true"><?= $escape($sidebarData['user']['avatar']); ?></div>
                <div class="profile-text">
                    <span class="name"><?= $escape($sidebarData['user']['fullName']); ?></span>
                    <?php if ($sidebarData['user']['username'] !== ''): ?>
                        <span class="username">@<?= $escape($sidebarData['user']['username']); ?></span>
                    <?php elseif ($sidebarData['user']['email'] !== ''): ?>
                        <span class="username"><?= $escape($sidebarData['user']['email']); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="profile-actions" data-open="false">
                <button class="dropdown-toggle" type="button" aria-expanded="false" aria-haspopup="true">
                    Profil Ayarları
                    <span aria-hidden="true">▾</span>
                </button>
                <div class="dropdown-menu" role="menu">
                    <a href="#" role="menuitem">Ayarlar</a>
                    <a href="logout.php" role="menuitem">Çıkış yap</a>
                </div>
            </div>
        </section>
    </aside>

    <script>
        const body = document.body;
        const sidebar = document.querySelector('#nexa-sidebar');
        const sidebarToggle = document.querySelector('.sidebar-toggle');
        const profileActions = document.querySelector('.profile-actions');
        const toggleButton = document.querySelector('.dropdown-toggle');
        const dropdownMenu = document.querySelector('.dropdown-menu');
        const navSubmenus = document.querySelectorAll('details.nav-submenu');

        const toggleText = sidebarToggle ? sidebarToggle.querySelector('.sidebar-toggle-text') : null;

        function updateToggleLabel(isCollapsed) {
            const label = isCollapsed ? 'Menüyü Göster' : 'Menüyü Gizle';
            if (sidebarToggle) {
                sidebarToggle.setAttribute('aria-label', label);
            }

            if (toggleText) {
                toggleText.textContent = label;
            }
        }

        if (sidebar && sidebarToggle) {
            sidebarToggle.addEventListener('click', () => {
                const isCollapsed = body.classList.toggle('sidebar-collapsed');
                sidebarToggle.setAttribute('aria-expanded', String(!isCollapsed));
                updateToggleLabel(isCollapsed);

                if (!isCollapsed && typeof sidebar.focus === 'function') {
                    sidebar.setAttribute('tabindex', '-1');
                    sidebar.focus({ preventScroll: true });
                    sidebar.removeAttribute('tabindex');
                }
            });

            updateToggleLabel(body.classList.contains('sidebar-collapsed'));
        }

        function closeDropdown(event) {
            if (!profileActions.contains(event.target)) {
                profileActions.dataset.open = 'false';
                toggleButton.setAttribute('aria-expanded', 'false');
                document.removeEventListener('click', closeDropdown);
            }
        }

        toggleButton.addEventListener('click', (event) => {
            const isOpen = profileActions.dataset.open === 'true';
            profileActions.dataset.open = String(!isOpen);
            toggleButton.setAttribute('aria-expanded', String(!isOpen));

            if (!isOpen) {
                document.addEventListener('click', closeDropdown);
            } else {
                document.removeEventListener('click', closeDropdown);
            }
        });

        navSubmenus.forEach((submenu) => {
            const summary = submenu.querySelector('summary');
            if (!summary) {
                return;
            }

            const updateAria = () => {
                summary.setAttribute('aria-expanded', submenu.open ? 'true' : 'false');
            };

            updateAria();
            submenu.addEventListener('toggle', updateAria);
        });
    </script>
</body>
</html>
