<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = isset($pageTitle) && $pageTitle !== '' ? $pageTitle : 'Nexa';
$userFullName = '';
if (isset($_SESSION['firstname'], $_SESSION['lastname'])) {
    $userFullName = trim($_SESSION['firstname'] . ' ' . $_SESSION['lastname']);
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
            gap: 12px;
            margin: 0;
            padding: 0;
        }

        .sidebar-nav a {
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

        .sidebar-nav a:hover,
        .sidebar-nav a:focus-visible {
            background: rgba(255, 255, 255, 0.15);
            outline: none;
        }

        .sidebar-section {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .sidebar-user {
            margin-top: auto;
            font-size: 0.95rem;
            background: rgba(255, 255, 255, 0.08);
            padding: 12px 14px;
            border-radius: 10px;
        }

        .sidebar-shortcut-hint {
            font-size: 0.8rem;
            color: rgba(249, 250, 251, 0.75);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .sidebar-shortcut-hint .hint-key {
            border: 1px solid rgba(249, 250, 251, 0.3);
            border-radius: 6px;
            padding: 4px 6px;
            font-size: 0.75rem;
            line-height: 1;
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
            const STORAGE_KEY = 'nexa-sidebar-collapsed';

            const setSidebarState = function (collapsed, persist = true) {
                body.classList.toggle('sidebar-collapsed', collapsed);
                body.setAttribute('data-sidebar', collapsed ? 'collapsed' : 'open');

                if (!persist) {
                    return;
                }

                try {
                    window.localStorage.setItem(STORAGE_KEY, collapsed ? '1' : '0');
                } catch (error) {
                    // Ignore storage errors (e.g. in private mode)
                }
            };

            const loadStoredState = function () {
                try {
                    return window.localStorage.getItem(STORAGE_KEY);
                } catch (error) {
                    return null;
                }
            };

            const storedState = loadStoredState();
            const hasStoredState = storedState === '1' || storedState === '0';

            if (hasStoredState) {
                setSidebarState(storedState === '1', false);
            } else if (window.innerWidth <= 768) {
                setSidebarState(true, false);
            } else {
                setSidebarState(false, false);
            }

            const toggleSidebar = function () {
                const collapsed = body.classList.contains('sidebar-collapsed');
                setSidebarState(!collapsed);
            };

            document.addEventListener('keydown', function (event) {
                if (!event.ctrlKey) {
                    return;
                }

                if (event.target && (event.target.tagName === 'INPUT' || event.target.tagName === 'TEXTAREA' || event.target.isContentEditable)) {
                    return;
                }

                if (event.key === '<' || event.key === ',' || event.code === 'Comma') {
                    event.preventDefault();
                    toggleSidebar();
                }
            });

            window.addEventListener('resize', function () {
                if (!hasStoredState && window.innerWidth > 1024 && body.classList.contains('sidebar-collapsed')) {
                    setSidebarState(false, false);
                }
            });
        });
    </script>
</head>
<body class="has-sidebar" data-sidebar="open">
    <aside class="sidebar" id="siteSidebar" aria-label="Ana navigasyon">
        <div class="sidebar-brand">Nexa</div>
        <nav class="sidebar-nav" aria-label="Site bağlantıları">
            <?php foreach ($publicLinks as $link) : ?>
                <a href="<?php echo htmlspecialchars($link['href'], ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8'); ?>
                </a>
            <?php endforeach; ?>
            <?php if ($isAuthenticated) : ?>
                <div class="sidebar-section" aria-label="Yetkili bağlantılar">
                    <?php foreach ($authenticatedLinks as $link) : ?>
                        <a href="<?php echo htmlspecialchars($link['href'], ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <div class="sidebar-section" aria-label="Ziyaretçi bağlantıları">
                    <?php foreach ($guestLinks as $link) : ?>
                        <a href="<?php echo htmlspecialchars($link['href'], ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </nav>
        <div class="sidebar-shortcut-hint" aria-hidden="true">
            <span class="hint-key">Ctrl</span>
            <span>+</span>
            <span class="hint-key">&lt;</span>
        </div>
        <?php if ($userFullName !== '') : ?>
            <div class="sidebar-user">Merhaba, <?php echo htmlspecialchars($userFullName, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
    </aside>
    <main class="main-content" id="mainContent" role="main">
