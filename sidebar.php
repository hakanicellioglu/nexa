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
    ['href' => 'dashboard.php', 'label' => 'Kontrol Paneli'],
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

$currentPage = basename($_SERVER['PHP_SELF'] ?? '');

if (! defined('NEXA_MONOTON_FONT_LOADED')) {
    include __DIR__ . '/fonts/monoton.php';
    define('NEXA_MONOTON_FONT_LOADED', true);
}
?>

<style>
    .sidebar {
        width: 260px;
        min-height: 100vh;
        background: #111827;
        color: #f9fafb;
        display: flex;
        flex-direction: column;
        padding: 24px 20px;
        box-sizing: border-box;
    }

    .sidebar .brand-title {
        font-family: 'Monoton', cursive;
        font-size: 2rem;
        letter-spacing: 0.08em;
        margin-bottom: 32px;
    }

    .sidebar .user-info {
        font-size: 0.95rem;
        margin-bottom: 24px;
        color: #d1d5db;
    }

    .sidebar nav {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .sidebar nav .nav-section-title {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #9ca3af;
        margin-top: 24px;
        margin-bottom: 8px;
    }

    .sidebar nav a {
        color: #f9fafb;
        text-decoration: none;
        padding: 10px 12px;
        border-radius: 8px;
        font-weight: 600;
        display: block;
        transition: background 0.2s ease, color 0.2s ease;
    }

    .sidebar nav a:hover {
        background: rgba(59, 130, 246, 0.25);
    }

    .sidebar nav a.active {
        background: #2563eb;
    }

    .sidebar .auth-actions {
        margin-top: 32px;
    }

    .sidebar .auth-actions a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        padding: 10px 12px;
        background: #2563eb;
        border-radius: 8px;
        color: #fff;
        text-decoration: none;
        font-weight: 600;
        transition: background 0.2s ease;
    }

    .sidebar .auth-actions a:hover {
        background: #1d4ed8;
    }
</style>

<aside class="sidebar">
    <div class="brand-title">Nexa</div>

    <?php if ($userFullName !== '') : ?>
        <div class="user-info">Merhaba, <?php echo htmlspecialchars($userFullName, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <nav>
        <?php foreach ($publicLinks as $link) :
            $isActive = $currentPage === basename($link['href']);
            ?>
            <a href="<?php echo htmlspecialchars($link['href'], ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo $isActive ? 'active' : ''; ?>">
                <?php echo htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8'); ?>
            </a>
        <?php endforeach; ?>

        <?php if ($isAuthenticated) : ?>
            <div class="nav-section-title">Yönetim</div>
            <?php foreach ($authenticatedLinks as $link) :
                $isActive = $currentPage === basename($link['href']);
                ?>
                <a href="<?php echo htmlspecialchars($link['href'], ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo $isActive ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8'); ?>
                </a>
            <?php endforeach; ?>
            <div class="auth-actions">
                <a href="logout.php">Çıkış Yap</a>
            </div>
        <?php else : ?>
            <div class="nav-section-title">Hesap</div>
            <?php foreach ($guestLinks as $link) :
                $isActive = $currentPage === basename($link['href']);
                ?>
                <a href="<?php echo htmlspecialchars($link['href'], ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo $isActive ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8'); ?>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </nav>
</aside>
