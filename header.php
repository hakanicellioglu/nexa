<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('nexaHeaderStyles')) {
    function nexaHeaderStyles(): void
    {
        if (defined('NEXA_HEADER_STYLE_OUTPUT')) {
            return;
        }

        define('NEXA_HEADER_STYLE_OUTPUT', true);

        echo <<<'STYLE'
<style>
    .nexa-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 2.5rem;
        background: #003840;
        color: #E8F9F3;
        box-shadow: 0 10px 24px rgba(0, 56, 64, 0.25);
    }

    .nexa-logo {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-weight: 700;
        font-size: 1.25rem;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        text-decoration: none;
        color: inherit;
    }

    .nexa-logo span {
        display: inline-flex;
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: linear-gradient(135deg, #007369, #02A676);
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        color: #FFFFFF;
    }

    .nexa-nav {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        margin: 0 2rem;
    }

    .nexa-nav a {
        color: #CDEAE5;
        text-decoration: none;
        font-weight: 500;
        letter-spacing: 0.01em;
        transition: color 0.2s ease;
    }

    .nexa-nav a:hover,
    .nexa-nav a:focus {
        color: #FFFFFF;
    }

    .nexa-profile-dropdown {
        position: relative;
        margin-left: auto;
    }

    .nexa-profile-dropdown > summary {
        list-style: none;
    }

    .nexa-profile-dropdown > summary::-webkit-details-marker {
        display: none;
    }

    .nexa-profile-toggle {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.6rem 1.4rem;
        border-radius: 999px;
        background: #008C72;
        color: #FFFFFF;
        border: none;
        cursor: pointer;
        font-weight: 600;
        letter-spacing: 0.02em;
        transition: background 0.2s ease, transform 0.2s ease;
    }

    .nexa-profile-toggle:focus,
    .nexa-profile-toggle:hover {
        background: #007369;
        transform: translateY(-1px);
    }

    .nexa-profile-toggle svg {
        width: 0.85rem;
        height: 0.85rem;
        fill: currentColor;
    }

    .nexa-profile-menu {
        position: absolute;
        right: 0;
        margin-top: 0.5rem;
        min-width: 180px;
        background: #005A5B;
        border-radius: 12px;
        box-shadow: 0 12px 24px rgba(0, 56, 64, 0.35);
        padding: 0.4rem;
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        z-index: 20;
    }

    .nexa-profile-menu a {
        display: block;
        padding: 0.6rem 0.9rem;
        border-radius: 8px;
        color: #E8F9F3;
        text-decoration: none;
        font-weight: 500;
        transition: background 0.2s ease;
    }

    .nexa-profile-menu a:hover,
    .nexa-profile-menu a:focus {
        background: rgba(0, 140, 114, 0.25);
    }

    .nexa-profile-dropdown:not([open]) .nexa-profile-menu {
        display: none;
    }

    @media (max-width: 768px) {
        .nexa-header {
            flex-direction: column;
            gap: 1rem;
        }

        .nexa-nav {
            flex-wrap: wrap;
            justify-content: center;
            margin: 0;
        }

        .nexa-profile-dropdown {
            width: 100%;
            display: flex;
            justify-content: center;
        }

        .nexa-profile-menu {
            position: static;
            width: 100%;
        }
    }
</style>
STYLE;
    }
}

if (!function_exists('renderNexaHeader')) {
    function renderNexaHeader(): void
    {
        nexaHeaderStyles();

        $profileLabel = $_SESSION['firstname'] ?? $_SESSION['username'] ?? 'Profil';

        echo sprintf(
            <<<'HTML'
<header class="nexa-header">
    <a class="nexa-logo" href="dashboard.php">
        <span>Nx</span>
        <div>Nexa Portal</div>
    </a>
    <nav class="nexa-nav" aria-label="Ana menü">
        <a href="#urunler">Ürünler</a>
        <a href="#fiyatlar">Fiyatlar</a>
        <a href="suppliers.php">Tedarikçiler</a>
        <a href="#projeler">Projeler</a>
        <a href="#siparisler">Siparişler</a>
    </nav>
    <details class="nexa-profile-dropdown">
        <summary>
            <span class="nexa-profile-toggle" role="button" aria-haspopup="true">
                %s
                <svg viewBox="0 0 20 20" aria-hidden="true" focusable="false">
                    <path d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.084l3.71-3.854a.75.75 0 0 1 1.08 1.04l-4.24 4.4a.75.75 0 0 1-1.08 0l-4.24-4.4a.75.75 0 0 1 .02-1.06z" />
                </svg>
            </span>
        </summary>
        <div class="nexa-profile-menu" role="menu">
            <a href="logout.php" role="menuitem">Çıkış Yap</a>
        </div>
    </details>
</header>
HTML,
            htmlspecialchars($profileLabel, ENT_QUOTES, 'UTF-8')
        );
    }
}
