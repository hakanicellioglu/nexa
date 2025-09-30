<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = isset($pageTitle) && $pageTitle !== '' ? $pageTitle : 'Nexa';
$userFirstName = trim($_SESSION['firstname'] ?? '');
$userLastName = trim($_SESSION['lastname'] ?? '');
$username = trim($_SESSION['username'] ?? '');
$stringFragment = static function (string $value, int $start, ?int $length = null): string {
    if ($value === '') {
        return '';
    }

    if (function_exists('mb_substr')) {
        return $length === null
            ? mb_substr($value, $start, null, 'UTF-8')
            : mb_substr($value, $start, $length, 'UTF-8');
    }

    return $length === null
        ? substr($value, $start)
        : substr($value, $start, $length);
};
$userFullName = '';
if ($userFirstName !== '' || $userLastName !== '') {
    $userFullName = trim($userFirstName . ' ' . $userLastName);
}
$profileInitials = '';
if ($userFirstName !== '' && $userLastName !== '') {
    $profileInitials = $stringFragment($userFirstName, 0, 1) . $stringFragment($userLastName, 0, 1);
} elseif ($userFirstName !== '') {
    $profileInitials = $stringFragment($userFirstName, 0, 2);
} elseif ($userLastName !== '') {
    $profileInitials = $stringFragment($userLastName, 0, 2);
} elseif ($username !== '') {
    $profileInitials = $stringFragment($username, 0, 2);
}
if ($profileInitials !== '') {
    $profileInitials = function_exists('mb_strtoupper')
        ? mb_strtoupper($profileInitials, 'UTF-8')
        : strtoupper($profileInitials);
}
$isAuthenticated = isset($_SESSION['user_id']);

$publicLinks = [
    ['href' => 'index.php', 'label' => 'Ana Sayfa'],
];

$authenticatedLinks = [
    ['href' => 'suppliers.php', 'label' => 'Tedarikçiler'],
    ['href' => 'products.php', 'label' => 'Ürünler'],
    ['href' => 'projects.php', 'label' => 'Projeler'],
    ['href' => 'price.php', 'label' => 'Fiyatlar'],
    ['href' => 'orders.php', 'label' => 'Siparişler'],
];

$guestLinks = [
    ['href' => 'login.php', 'label' => 'Giriş Yap'],
    ['href' => 'register.php', 'label' => 'Kayıt Ol'],
];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <?php include __DIR__ . '/fonts/monoton.php'; ?>
    <style>
        :root {
            color-scheme: light;
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: #f3f4f6;
            color: #111827;
            font-family: Arial, sans-serif;
        }

        body {
            line-height: 1.5;
        }

        body.has-sidebar {
            padding: 0;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 260px;
            background: #1f2937;
            color: #f9fafb;
            display: flex;
            flex-direction: column;
            gap: 24px;
            padding: 28px 24px;
            transform: translateX(0);
            transition: transform 0.3s ease;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.45);
            z-index: 1000;
        }

        .sidebar-brand {
            font-family: 'Monoton', cursive;
            font-size: 2rem;
            letter-spacing: 0.08em;
            margin: 0;
            white-space: nowrap;
        }

        .sidebar-nav {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin: 0;
            padding: 0;
        }

        .sidebar-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .sidebar-group-title {
            margin: 0;
            font-size: 0.75rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: rgba(249, 250, 251, 0.65);
        }

        .sidebar-group-links {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .sidebar-group-links a {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 8px;
            color: inherit;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.2s ease, color 0.2s ease;
        }

        .sidebar-group-links a:hover,
        .sidebar-group-links a:focus-visible {
            background: rgba(255, 255, 255, 0.15);
            outline: none;
        }

        .sidebar-profile-wrapper {
            margin-top: auto;
            position: relative;
        }

        .sidebar-profile {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            border-radius: 12px;
            border: none;
            background: rgba(255, 255, 255, 0.08);
            color: inherit;
            cursor: pointer;
            font: inherit;
            text-align: left;
            transition: background 0.2s ease;
        }

        .sidebar-profile:hover,
        .sidebar-profile:focus-visible {
            background: rgba(255, 255, 255, 0.16);
            outline: none;
        }

        .sidebar-profile-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.18);
            font-weight: 700;
            font-size: 0.95rem;
        }

        .sidebar-profile-info {
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .sidebar-profile-name {
            font-weight: 600;
            font-size: 0.95rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-profile-username {
            font-size: 0.8rem;
            color: rgba(249, 250, 251, 0.65);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-profile-caret {
            margin-left: auto;
            display: inline-flex;
            align-items: center;
        }

        .sidebar-profile-menu {
            position: absolute;
            left: 0;
            right: 0;
            top: calc(100% + 8px);
            background: #111827;
            border-radius: 12px;
            box-shadow: 0 18px 35px rgba(15, 23, 42, 0.35);
            padding: 8px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .sidebar-profile-menu a {
            padding: 10px 12px;
            border-radius: 8px;
            color: inherit;
            text-decoration: none;
            font-size: 0.9rem;
            transition: background 0.2s ease;
        }

        .sidebar-profile-menu a:hover,
        .sidebar-profile-menu a:focus-visible {
            background: rgba(255, 255, 255, 0.12);
            outline: none;
        }

        main.main-content {
            display: block;
            padding: 48px 36px;
            margin: 0;
            margin-left: 260px;
            width: calc(100% - 260px);
            max-width: 960px;
            transition: margin-left 0.3s ease, max-width 0.3s ease, padding 0.3s ease, width 0.3s ease;
        }

        a.button {
            display: inline-block;
            margin-top: 16px;
            padding: 10px 18px;
            background: #2563eb;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            transition: background 0.2s ease;
        }

        a.button:hover {
            background: #1d4ed8;
        }

        .card {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
        }

        body.sidebar-collapsed .sidebar {
            transform: translateX(-100%);
        }

        body.sidebar-collapsed main.main-content {
            margin-left: 0;
            width: 100%;
            max-width: 1000px;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 240px;
                box-shadow: 0 20px 45px rgba(15, 23, 42, 0.35);
            }

            main.main-content {
                padding: 32px 20px;
                margin-left: 0;
                width: 100%;
            }

            body:not(.sidebar-collapsed) .sidebar {
                transform: translateX(0);
            }
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const body = document.body;
            const setSidebarState = function (collapsed) {
                body.classList.toggle('sidebar-collapsed', collapsed);
                body.setAttribute('data-sidebar', collapsed ? 'collapsed' : 'open');
            };

            const applyResponsiveState = function () {
                const shouldCollapse = window.innerWidth <= 768;
                setSidebarState(shouldCollapse);
            };

            applyResponsiveState();

            window.addEventListener('resize', function () {
                applyResponsiveState();
            });

            const profileButton = document.getElementById('sidebarProfileButton');
            const profileMenu = document.getElementById('sidebarProfileMenu');

            if (profileButton && profileMenu) {
                const closeMenu = function () {
                    profileButton.setAttribute('aria-expanded', 'false');
                    profileMenu.hidden = true;
                };

                profileButton.addEventListener('click', function (event) {
                    event.stopPropagation();
                    const isExpanded = profileButton.getAttribute('aria-expanded') === 'true';
                    if (isExpanded) {
                        closeMenu();
                    } else {
                        profileButton.setAttribute('aria-expanded', 'true');
                        profileMenu.hidden = false;
                        const firstMenuItem = profileMenu.querySelector('[role="menuitem"]');
                        if (firstMenuItem && event.detail === 0) {
                            firstMenuItem.focus();
                        }
                    }
                });

                document.addEventListener('click', function (event) {
                    if (!profileMenu.contains(event.target) && !profileButton.contains(event.target)) {
                        closeMenu();
                    }
                });

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape') {
                        closeMenu();
                        profileButton.focus();
                    }
                });

                closeMenu();
            }
        });
    </script>
</head>
<body class="has-sidebar" data-sidebar="open">
    <aside class="sidebar" id="siteSidebar" aria-label="Ana navigasyon">
        <div class="sidebar-brand">Nexa</div>
        <nav class="sidebar-nav" aria-label="Site bağlantıları">
            <div class="sidebar-group" aria-label="Genel">
                <p class="sidebar-group-title">Genel</p>
                <div class="sidebar-group-links">
                    <?php foreach ($publicLinks as $link) : ?>
                        <a href="<?php echo htmlspecialchars($link['href'], ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php if ($isAuthenticated) : ?>
                <div class="sidebar-group" aria-label="Yönetim">
                    <p class="sidebar-group-title">Yönetim</p>
                    <div class="sidebar-group-links">
                        <?php foreach ($authenticatedLinks as $link) : ?>
                            <a href="<?php echo htmlspecialchars($link['href'], ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else : ?>
                <div class="sidebar-group" aria-label="Üyelik">
                    <p class="sidebar-group-title">Üyelik</p>
                    <div class="sidebar-group-links">
                        <?php foreach ($guestLinks as $link) : ?>
                            <a href="<?php echo htmlspecialchars($link['href'], ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </nav>
        <?php if ($isAuthenticated) : ?>
            <div class="sidebar-profile-wrapper">
                <button class="sidebar-profile" id="sidebarProfileButton" type="button" aria-haspopup="menu" aria-expanded="false">
                    <?php if ($profileInitials !== '') : ?>
                        <span class="sidebar-profile-avatar" aria-hidden="true"><?php echo htmlspecialchars($profileInitials, ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endif; ?>
                    <span class="sidebar-profile-info">
                        <span class="sidebar-profile-name">
                            <?php echo htmlspecialchars($userFullName !== '' ? $userFullName : $username, ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                        <?php if ($username !== '') : ?>
                            <span class="sidebar-profile-username">@<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                    </span>
                    <span class="sidebar-profile-caret" aria-hidden="true">▾</span>
                </button>
                <div class="sidebar-profile-menu" id="sidebarProfileMenu" role="menu" aria-labelledby="sidebarProfileButton" hidden>
                    <a href="settings.php" role="menuitem">Ayarlar</a>
                    <a href="logout.php" role="menuitem">Çıkış Yap</a>
                </div>
            </div>
        <?php endif; ?>
    </aside>
    <main class="main-content" id="mainContent" role="main">
