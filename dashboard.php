<?php
// Dashboard page for Nexa platform

declare(strict_types=1);

session_start();

if (empty($_SESSION['user']) && empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user = (array)($_SESSION['user'] ?? []);

$sidebarData = (static function (string $sidebarPath): array {
    ob_start();
    include $sidebarPath;
    $sidebarHtml = (string)ob_get_clean();

    $styles = '';
    if (preg_match('/<style\b[^>]*>(.*?)<\/style>/si', $sidebarHtml, $styleMatches)) {
        $styles = trim((string)$styleMatches[1]);
    }

    $body = '';
    if (preg_match('/<body\b[^>]*>(.*?)<\/body>/si', $sidebarHtml, $bodyMatches)) {
        $body = trim((string)$bodyMatches[1]);
    } else {
        $body = trim($sidebarHtml);
    }

    return [
        'styles' => $styles,
        'body' => $body,
    ];
})(__DIR__ . '/sidebar.php');

$sidebarStyles = $sidebarData['styles'];
if ($sidebarStyles !== '') {
    $sidebarStyles = preg_replace('/body\s*\{[^}]*\}\s*/si', '', $sidebarStyles, 1) ?? $sidebarStyles;
}

$sidebarMarkup = $sidebarData['body'];

if ($sidebarMarkup !== '') {
    if (preg_match('/<script\b[^>]*>.*?<\/script>/si', $sidebarMarkup, $scriptMatches)) {
        $sidebarMarkup = trim(str_replace($scriptMatches[0], '', $sidebarMarkup));
    }
}

$fullName = trim((string)($user['firstname'] ?? '') . ' ' . (string)($user['lastname'] ?? ''));
if ($fullName === '') {
    $fullName = 'Misafir Kullanıcı';
}

$username = (string)($user['username'] ?? '');
$email = (string)($user['email'] ?? '');

$profileDetails = array_filter([
    'Kullanıcı Adı' => $username !== '' ? $username : null,
    'E-posta' => $email !== '' ? $email : null,
]);

$flashMessages = [];
if (!empty($_SESSION['flash']) && is_array($_SESSION['flash'])) {
    $flashMessages = array_filter(array_map('strval', $_SESSION['flash']));
    unset($_SESSION['flash']);
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexa - Kontrol Paneli</title>
    <?php include __DIR__ . '/fonts/monoton.php'; ?>
    <style>
        <?php include __DIR__ . '/assets/css/root.css'; ?>

        <?php if ($sidebarStyles !== ''): ?>
            <?= $sidebarStyles ?>
        <?php endif; ?>

        body {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.08), rgba(14, 165, 233, 0.08));
            color: var(--text-primary);
        }

        .dashboard-layout {
            display: grid;
            grid-template-columns: minmax(280px, 320px) 1fr;
            min-height: 100vh;
        }

        .dashboard-main {
            padding: var(--spacing-2xl);
            background-color: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(12px);
            display: flex;
            flex-direction: column;
            gap: var(--spacing-2xl);
        }

        .dashboard-header {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-sm);
        }

        .dashboard-header h1 {
            font-size: var(--font-size-3xl);
        }

        .dashboard-header p {
            color: var(--text-secondary);
            max-width: 520px;
        }

        .flash-messages {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-sm);
        }

        .flash-message {
            padding: var(--spacing-md) var(--spacing-lg);
            border-radius: var(--radius-lg);
            background-color: rgba(34, 197, 94, 0.12);
            border: 1px solid rgba(34, 197, 94, 0.35);
            color: var(--color-success, #047857);
            font-weight: var(--font-weight-medium);
        }

        .profile-card {
            background-color: var(--bg-primary);
            border-radius: var(--radius-xl);
            border: 1px solid var(--border-secondary);
            box-shadow: var(--shadow-md);
            padding: var(--spacing-xl);
            display: flex;
            flex-direction: column;
            gap: var(--spacing-lg);
        }

        .profile-card header {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-xs);
        }

        .profile-card h2 {
            font-size: var(--font-size-xl);
            margin: 0;
        }

        .profile-meta {
            color: var(--text-secondary);
            font-size: var(--font-size-sm);
        }

        .profile-details {
            display: grid;
            gap: var(--spacing-md);
        }

        .profile-detail {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-2xs);
        }

        .profile-detail span:first-child {
            font-size: var(--font-size-xs);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-tertiary);
        }

        .profile-detail span:last-child {
            font-size: var(--font-size-lg);
            font-weight: var(--font-weight-semibold);
            color: var(--text-primary);
        }

        @media (max-width: 960px) {
            .dashboard-layout {
                grid-template-columns: 1fr;
            }

            .dashboard-main {
                padding: var(--spacing-xl);
            }

            .sidebar {
                position: sticky;
                top: 0;
                z-index: var(--z-index-sticky);
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <?= $sidebarMarkup ?>
        <main class="dashboard-main" aria-label="Kontrol paneli ana içerik">
            <header class="dashboard-header">
                <h1>Hoş geldiniz, <?= htmlspecialchars($fullName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?> 👋</h1>
                <p>Hesabınızın bilgilerini buradan görüntüleyebilir ve Nexa deneyiminizi yönetmeye devam edebilirsiniz.</p>
            </header>

            <?php if ($flashMessages): ?>
                <section class="flash-messages" aria-live="polite">
                    <?php foreach ($flashMessages as $message): ?>
                        <div class="flash-message"><?= htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></div>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>

            <section class="profile-card" aria-label="Profil bilgileri">
                <header>
                    <h2><?= htmlspecialchars($fullName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></h2>
                    <?php if ($username !== ''): ?>
                        <p class="profile-meta">@<?= htmlspecialchars($username, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
                    <?php endif; ?>
                </header>

                <?php if ($profileDetails): ?>
                    <div class="profile-details">
                        <?php foreach ($profileDetails as $label => $value): ?>
                            <div class="profile-detail">
                                <span><?= htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></span>
                                <span><?= htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="profile-meta">Profil bilgileri henüz tamamlanmadı.</p>
                <?php endif; ?>
            </section>
        </main>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const dropdownToggle = document.querySelector('.dropdown-toggle');
            const profileActions = document.querySelector('.profile-actions');

            if (!dropdownToggle || !profileActions) {
                return;
            }

            const dropdownMenu = profileActions.querySelector('.dropdown-menu');

            function closeOnOutsideClick(event) {
                if (!profileActions.contains(event.target)) {
                    profileActions.dataset.open = 'false';
                    dropdownToggle.setAttribute('aria-expanded', 'false');
                    document.removeEventListener('click', closeOnOutsideClick);
                }
            }

            dropdownToggle.addEventListener('click', (event) => {
                event.stopPropagation();
                const isOpen = profileActions.dataset.open === 'true';
                profileActions.dataset.open = String(!isOpen);
                dropdownToggle.setAttribute('aria-expanded', String(!isOpen));

                if (!isOpen) {
                    document.addEventListener('click', closeOnOutsideClick);
                    if (dropdownMenu) {
                        dropdownMenu.setAttribute('aria-hidden', 'false');
                    }
                } else {
                    document.removeEventListener('click', closeOnOutsideClick);
                    if (dropdownMenu) {
                        dropdownMenu.setAttribute('aria-hidden', 'true');
                    }
                }
            });
        });
    </script>
</body>
</html>
