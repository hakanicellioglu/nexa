<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('MONOTON_FONT_INCLUDED')) {
    include __DIR__ . '/fonts/monoton.php';
    define('MONOTON_FONT_INCLUDED', true);
}

if (!defined('BOOTSTRAP_CDN_INCLUDED')) {
    include __DIR__ . '/cdn/bootstrap.php';
    define('BOOTSTRAP_CDN_INCLUDED', true);
}

if (!defined('BOOTSTRAP_ICONS_INCLUDED')) {
    echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">';
    define('BOOTSTRAP_ICONS_INCLUDED', true);
}

$fullName = trim(($_SESSION['firstname'] ?? '') . ' ' . ($_SESSION['lastname'] ?? ''));
$username = $_SESSION['username'] ?? 'kullanici';
?>
<aside class="sidebar bg-white border-end shadow-sm">
    <div class="sidebar-header p-4 border-bottom text-center">
        <h1 class="fs-2 mb-0" style="font-family: 'Monoton', cursive; letter-spacing: 4px;">NEXA</h1>
        <p class="text-muted mb-0">Yönetim Paneli</p>
    </div>
    <div class="profile-section d-flex align-items-center gap-3 p-4 border-bottom">
        <div class="profile-avatar d-flex align-items-center justify-content-center rounded-circle text-white fw-bold">
            <?= htmlspecialchars(strtoupper(mb_substr($fullName !== '' ? $fullName : $username, 0, 1, 'UTF-8')), ENT_QUOTES, 'UTF-8') ?>
        </div>
        <div class="flex-grow-1">
            <div class="fw-semibold text-dark">
                <?= htmlspecialchars($fullName !== '' ? $fullName : 'Ad Soyad', ENT_QUOTES, 'UTF-8') ?>
            </div>
            <div class="text-muted small">@<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <div class="dropdown">
            <button class="btn btn-link text-decoration-none text-dark p-0" type="button" id="profileMenu" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-three-dots-vertical"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileMenu">
                <li><a class="dropdown-item" href="#">Ayarlar</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="logout.php">Çıkış yap</a></li>
            </ul>
        </div>
    </div>
    <nav class="sidebar-nav p-4">
        <div class="nav-group mb-4">
            <h2 class="text-uppercase text-muted fs-6 fw-semibold mb-3">Genel</h2>
            <a href="dashboard.php" class="nav-link d-flex align-items-center gap-2 mb-2">
                <span class="icon-circle"><i class="bi bi-house-door"></i></span>
                <span>Anasayfa</span>
            </a>
        </div>
        <div class="nav-group mb-4">
            <h2 class="text-uppercase text-muted fs-6 fw-semibold mb-3">Katalog</h2>
            <div class="submenu">
                <button class="submenu-toggle nav-link d-flex w-100 align-items-center justify-content-between" data-bs-toggle="collapse" data-bs-target="#urunlerSubmenu" aria-expanded="true">
                    <span class="d-flex align-items-center gap-2"><span class="icon-circle"><i class="bi bi-box"></i></span>Ürünler</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <div class="collapse show" id="urunlerSubmenu">
                    <a href="#" class="nav-link ms-4">Ürün Listesi</a>
                    <a href="#" class="nav-link ms-4">Yeni Ürün</a>
                </div>
            </div>
            <div class="submenu mt-3">
                <button class="submenu-toggle nav-link d-flex w-100 align-items-center justify-content-between" data-bs-toggle="collapse" data-bs-target="#tedarikcilerSubmenu" aria-expanded="false">
                    <span class="d-flex align-items-center gap-2"><span class="icon-circle"><i class="bi bi-people"></i></span>Tedarikçiler</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <div class="collapse" id="tedarikcilerSubmenu">
                    <a href="#" class="nav-link ms-4">Tedarikçi Listesi</a>
                    <a href="#" class="nav-link ms-4">Tedarikçi Davet Et</a>
                </div>
            </div>
        </div>
        <div class="nav-group mb-4">
            <h2 class="text-uppercase text-muted fs-6 fw-semibold mb-3">Finans</h2>
            <div class="submenu">
                <button class="submenu-toggle nav-link d-flex w-100 align-items-center justify-content-between" data-bs-toggle="collapse" data-bs-target="#fiyatlarSubmenu" aria-expanded="false">
                    <span class="d-flex align-items-center gap-2"><span class="icon-circle"><i class="bi bi-cash"></i></span>Fiyatlar</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <div class="collapse" id="fiyatlarSubmenu">
                    <a href="#" class="nav-link ms-4">Fiyat Listesi</a>
                    <a href="#" class="nav-link ms-4">Fiyat Karşılaştırma</a>
                </div>
            </div>
        </div>
        <div class="nav-group">
            <h2 class="text-uppercase text-muted fs-6 fw-semibold mb-3">Operasyon</h2>
            <div class="submenu mb-3">
                <button class="submenu-toggle nav-link d-flex w-100 align-items-center justify-content-between" data-bs-toggle="collapse" data-bs-target="#projelerSubmenu" aria-expanded="false">
                    <span class="d-flex align-items-center gap-2"><span class="icon-circle"><i class="bi bi-kanban"></i></span>Projeler</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <div class="collapse" id="projelerSubmenu">
                    <a href="#" class="nav-link ms-4">Proje Panosu</a>
                    <a href="#" class="nav-link ms-4">Yeni Proje</a>
                </div>
            </div>
            <div class="submenu">
                <button class="submenu-toggle nav-link d-flex w-100 align-items-center justify-content-between" data-bs-toggle="collapse" data-bs-target="#siparislerSubmenu" aria-expanded="false">
                    <span class="d-flex align-items-center gap-2"><span class="icon-circle"><i class="bi bi-clipboard-data"></i></span>Siparişler</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <div class="collapse" id="siparislerSubmenu">
                    <a href="#" class="nav-link ms-4">Sipariş Takibi</a>
                    <a href="#" class="nav-link ms-4">Yeni Sipariş</a>
                </div>
            </div>
        </div>
    </nav>
</aside>

<style>
    .sidebar {
        min-height: 100vh;
        width: 100%;
        max-width: 280px;
        position: relative;
        font-size: 0.95rem;
    }

    .profile-avatar {
        width: 56px;
        height: 56px;
        background: linear-gradient(135deg, #0d6efd, #6610f2);
        font-size: 1.5rem;
    }

    .sidebar-header p {
        font-size: 0.85rem;
    }

    .nav-group h2 {
        font-size: 0.75rem;
        letter-spacing: 0.05em;
    }

    .profile-section .fw-semibold {
        font-size: 0.95rem;
    }

    .nav-link {
        color: #343a40;
        padding: 0.75rem 1rem;
        border-radius: 0.75rem;
        transition: background-color 0.2s ease, color 0.2s ease;
        font-size: 0.9rem;
    }

    .nav-link:hover,
    .nav-link:focus {
        background-color: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
    }

    .nav-link.active {
        background-color: #0d6efd;
        color: #fff;
    }

    .icon-circle {
        display: inline-flex;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        align-items: center;
        justify-content: center;
        background-color: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
    }

    .submenu .nav-link {
        padding-left: 0.5rem;
        font-size: 0.85rem;
    }

    .submenu-toggle {
        background: transparent;
        border: none;
        padding: 0.75rem 1rem;
        text-align: left;
        color: #343a40;
        font-size: 0.9rem;
    }

    .submenu-toggle:hover,
    .submenu-toggle:focus {
        background-color: rgba(13, 110, 253, 0.08);
        color: #0d6efd;
    }

    @media (max-width: 992px) {
        .sidebar {
            max-width: 100%;
            border-right: none;
        }
    }
</style>
