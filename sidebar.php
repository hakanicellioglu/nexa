<?php
// Sidebar component for Nexa platform
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

        .nav-item > a[aria-current="page"] {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.12), rgba(14, 165, 233, 0.12));
            color: var(--color-secondary-dark);
        }

        details.nav-submenu {
            border-radius: var(--radius-lg);
            background-color: transparent;
        }

        details.nav-submenu[open] {
            background-color: var(--surface-secondary);
        }

        details.nav-submenu > summary {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: var(--spacing-sm) var(--spacing-md);
            cursor: pointer;
            list-style: none;
        }

        details.nav-submenu > summary::-webkit-details-marker {
            display: none;
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
    <aside class="sidebar" aria-label="Ana menü">
        <header class="brand">
            <div class="brand-logo" aria-hidden="true">N</div>
            <div class="brand-name">
                <span>Nexa</span>
                <span>Yönetim Paneli</span>
            </div>
        </header>

        <nav>
            <section class="nav-group" aria-labelledby="nav-general">
                <h2 id="nav-general" class="nav-group-title">Genel</h2>
                <ul class="nav-list">
                    <li class="nav-item"><a href="#" aria-current="page">Anasayfa</a></li>
                    <li class="nav-item"><a href="#">Ürünler</a></li>
                    <li class="nav-item"><a href="#">Tedarikçiler</a></li>
                </ul>
            </section>

            <section class="nav-group" aria-labelledby="nav-operations">
                <h2 id="nav-operations" class="nav-group-title">Operasyon</h2>
                <ul class="nav-list">
                    <li class="nav-item"><a href="#">Fiyatlar</a></li>
                    <li class="nav-item">
                        <details class="nav-submenu">
                            <summary>Projeler <span aria-hidden="true">▾</span></summary>
                            <ul class="submenu-list">
                                <li><a href="#">Aktif Projeler</a></li>
                                <li><a href="#">Bekleyenler</a></li>
                                <li><a href="#">Tamamlananlar</a></li>
                            </ul>
                        </details>
                    </li>
                    <li class="nav-item"><a href="#">Siparişler</a></li>
                </ul>
            </section>
        </nav>

        <section class="profile" aria-label="Profil">
            <div class="profile-info">
                <div class="avatar" aria-hidden="true">OA</div>
                <div class="profile-text">
                    <span class="name">Onur Aydın</span>
                    <span class="username">@kullanici_adi</span>
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
        const profileActions = document.querySelector('.profile-actions');
        const toggleButton = document.querySelector('.dropdown-toggle');
        const dropdownMenu = document.querySelector('.dropdown-menu');

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
    </script>
</body>
</html>
