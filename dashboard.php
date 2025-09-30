<?php
declare(strict_types=1);

session_start();

if (empty($_SESSION['user']) && empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

/**
 * @param string $value
 */
$escape = static function (string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};

$user = (array)($_SESSION['user'] ?? []);
$firstName = trim((string)($user['firstname'] ?? ''));
$lastName = trim((string)($user['lastname'] ?? ''));
$fullName = trim($firstName . ' ' . $lastName);

if ($fullName === '') {
    $fullName = 'Misafir Kullanıcı';
}

$username = trim((string)($user['username'] ?? ''));
$email = trim((string)($user['email'] ?? ''));

$profileDetails = array_filter([
    'Kullanıcı Adı' => $username,
    'E-posta' => $email,
]);

/**
 * @return array{markup: string, styles: string, scripts: string}
 */
$loadSidebar = static function (string $sidebarPath): array {
    if (!is_file($sidebarPath)) {
        return [
            'markup' => '',
            'styles' => '',
            'scripts' => '',
        ];
    }

    ob_start();
    include $sidebarPath;
    $sidebarHtml = (string)ob_get_clean();

    if ($sidebarHtml === '') {
        return [
            'markup' => '',
            'styles' => '',
            'scripts' => '',
        ];
    }

    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $loaded = $dom->loadHTML($sidebarHtml, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    if (!$loaded) {
        return [
            'markup' => $sidebarHtml,
            'styles' => '',
            'scripts' => '',
        ];
    }

    $styles = [];
    foreach ($dom->getElementsByTagName('style') as $styleNode) {
        $styles[] = $styleNode->textContent ?? '';
    }

    $scripts = [];
    foreach ($dom->getElementsByTagName('script') as $scriptNode) {
        $scripts[] = $dom->saveHTML($scriptNode) ?: '';
    }

    $xpath = new DOMXPath($dom);
    $asideNode = $xpath->query('//aside')->item(0);

    $markup = '';
    if ($asideNode instanceof DOMNode) {
        $markup = trim((string)$dom->saveHTML($asideNode));
    } else {
        $bodyNode = $dom->getElementsByTagName('body')->item(0);
        if ($bodyNode instanceof DOMNode) {
            $markup = trim((string)$dom->saveHTML($bodyNode));
        } else {
            $markup = trim($sidebarHtml);
        }
    }

    $styleContent = trim(implode("\n", $styles));
    if ($styleContent !== '') {
        $styleContent = preg_replace('/body\s*\{[^}]*\}\s*/si', '', $styleContent) ?? $styleContent;
    }

    $scriptContent = trim(implode("\n", $scripts));

    return [
        'markup' => $markup,
        'styles' => $styleContent,
        'scripts' => $scriptContent,
    ];
};

$sidebarAssets = $loadSidebar(__DIR__ . '/sidebar.php');

$welcomeMessage = $firstName !== ''
    ? sprintf('Bugün ne planlamak istersiniz, %s?', $escape($firstName))
    : 'Güncel gelişmeleri kontrol etmek için harika bir zaman.';

$today = new DateTimeImmutable('now', new DateTimeZone('Europe/Istanbul'));

$summaryCards = [
    [
        'title' => 'Bekleyen İşlemler',
        'value' => '3',
        'description' => 'Bugün tamamlanması önerilen görev sayısı.',
    ],
    [
        'title' => 'Toplam Proje',
        'value' => '12',
        'description' => 'Aktif ve planlanan tüm projeleriniz.',
    ],
    [
        'title' => 'Son Oturum',
        'value' => $today->format('d.m.Y H:i'),
        'description' => 'Bu cihazdan kaydedilen son giriş zamanı.',
    ],
];

$quickActions = [
    [
        'label' => 'Yeni Proje Oluştur',
        'href' => '#',
        'description' => 'Takımınızı organize etmek için hızlı bir başlangıç yapın.',
    ],
    [
        'label' => 'Tedarikçi Ekle',
        'href' => '#',
        'description' => 'Tedarik ağınızı genişletin ve izleyin.',
    ],
    [
        'label' => 'Fiyat Analizi Başlat',
        'href' => '#',
        'description' => 'Pazar trendlerini karşılaştırın ve raporlayın.',
    ],
];

$activityFeed = [
    [
        'time' => 'Bugün',
        'title' => 'Kontrol paneline giriş yapıldı',
        'description' => 'Hesabınıza güvenli giriş sağladınız.',
    ],
    [
        'time' => 'Dün',
        'title' => 'Proje taslağı kaydedildi',
        'description' => 'Yeni bir proje planı oluşturuldu ve taslak olarak saklandı.',
    ],
    [
        'time' => 'Geçen Hafta',
        'title' => 'Tedarikçi listesi güncellendi',
        'description' => 'Tedarikçi performans raporu gözden geçirildi.',
    ],
];

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

        <?php if ($sidebarAssets['styles'] !== ''): ?>
            <?= $sidebarAssets['styles']; ?>
        <?php endif; ?>

        body {
            margin: 0;
            background: linear-gradient(180deg, rgba(59, 130, 246, 0.08), rgba(14, 165, 233, 0.08));
            color: var(--text-primary);
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .dashboard-layout {
            display: grid;
            grid-template-columns: minmax(280px, 320px) 1fr;
            min-height: 100vh;
            overflow: hidden;
        }

        .dashboard-main {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-2xl);
            padding: var(--spacing-3xl);
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(14px);
        }

        .page-header {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-sm);
        }

        .page-title {
            font-size: clamp(2.2rem, 3vw, 3rem);
            font-weight: var(--font-weight-bold);
            margin: 0;
        }

        .page-subtitle {
            margin: 0;
            font-size: var(--font-size-lg);
            color: var(--text-secondary);
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: var(--spacing-xl);
        }

        .summary-card {
            padding: var(--spacing-xl);
            border-radius: var(--radius-2xl);
            background: rgba(59, 130, 246, 0.08);
            border: 1px solid rgba(59, 130, 246, 0.25);
            box-shadow: 0 20px 50px -25px rgba(15, 23, 42, 0.35);
            transition: transform 200ms ease, box-shadow 200ms ease;
        }

        .summary-card:hover,
        .summary-card:focus-within {
            transform: translateY(-4px);
            box-shadow: 0 24px 65px -30px rgba(15, 23, 42, 0.4);
        }

        .summary-card__label {
            font-size: var(--font-size-sm);
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--text-tertiary);
            margin-bottom: var(--spacing-xs);
        }

        .summary-card__value {
            font-size: clamp(1.75rem, 4vw, 2.4rem);
            font-weight: var(--font-weight-semibold);
            margin: 0;
            color: var(--text-primary);
        }

        .summary-card__description {
            margin-top: var(--spacing-sm);
            font-size: var(--font-size-sm);
            color: var(--text-secondary);
        }

        .grid-two-columns {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: var(--spacing-2xl);
            align-items: start;
        }

        .panel {
            background: rgba(255, 255, 255, 0.72);
            border-radius: var(--radius-2xl);
            padding: var(--spacing-2xl);
            border: 1px solid rgba(148, 163, 184, 0.35);
            box-shadow: 0 20px 45px -24px rgba(15, 23, 42, 0.25);
            display: flex;
            flex-direction: column;
            gap: var(--spacing-lg);
        }

        .panel h2 {
            margin: 0;
            font-size: clamp(1.4rem, 2.5vw, 1.8rem);
            font-weight: var(--font-weight-semibold);
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: var(--spacing-lg);
        }

        .quick-action {
            border-radius: var(--radius-xl);
            border: 1px dashed rgba(59, 130, 246, 0.35);
            padding: var(--spacing-lg);
            background: rgba(59, 130, 246, 0.06);
            display: flex;
            flex-direction: column;
            gap: var(--spacing-sm);
            transition: border-color 200ms ease, background 200ms ease;
        }

        .quick-action a {
            color: inherit;
            text-decoration: none;
            font-weight: var(--font-weight-semibold);
            font-size: var(--font-size-lg);
        }

        .quick-action:hover,
        .quick-action:focus-within {
            border-color: rgba(59, 130, 246, 0.65);
            background: rgba(59, 130, 246, 0.12);
        }

        .quick-action p {
            margin: 0;
            font-size: var(--font-size-sm);
            color: var(--text-secondary);
        }

        .profile-summary {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-md);
        }

        .profile-summary__header {
            display: flex;
            align-items: center;
            gap: var(--spacing-lg);
        }

        .profile-summary__avatar {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.85), rgba(14, 165, 233, 0.85));
            display: grid;
            place-items: center;
            font-size: 1.5rem;
            font-weight: var(--font-weight-semibold);
            color: #fff;
        }

        .profile-summary__name {
            margin: 0;
            font-size: 1.5rem;
            font-weight: var(--font-weight-semibold);
        }

        .profile-summary__meta {
            margin: 0;
            color: var(--text-secondary);
        }

        .profile-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: var(--spacing-lg);
        }

        .profile-detail {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-2xs);
        }

        .profile-detail span:first-child {
            font-size: var(--font-size-xs);
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--text-tertiary);
        }

        .profile-detail span:last-child {
            font-size: var(--font-size-lg);
            font-weight: var(--font-weight-medium);
        }

        .activity-feed {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-lg);
        }

        .activity-item {
            display: flex;
            gap: var(--spacing-md);
            padding-bottom: var(--spacing-lg);
            border-bottom: 1px solid rgba(148, 163, 184, 0.25);
        }

        .activity-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .activity-item__time {
            min-width: 120px;
            font-size: var(--font-size-xs);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-tertiary);
        }

        .activity-item__title {
            margin: 0 0 var(--spacing-2xs);
            font-weight: var(--font-weight-semibold);
        }

        .activity-item__description {
            margin: 0;
            color: var(--text-secondary);
            font-size: var(--font-size-sm);
        }

        @media (max-width: 1024px) {
            .dashboard-layout {
                grid-template-columns: 1fr;
            }

            .dashboard-main {
                padding: var(--spacing-2xl);
            }

            #nexa-sidebar {
                position: sticky;
                top: 0;
                z-index: var(--z-index-sticky);
            }
        }

        @media (max-width: 640px) {
            .dashboard-main {
                padding: var(--spacing-xl);
            }

            .activity-item {
                flex-direction: column;
            }

            .activity-item__time {
                min-width: initial;
            }
        }
    </style>
</head>
<body>
<div class="dashboard-layout">
    <?= $sidebarAssets['markup']; ?>
    <main class="dashboard-main" aria-label="Kontrol paneli ana içerik">
        <header class="page-header">
            <h1 class="page-title">Hoş geldiniz, <?= $escape($fullName); ?> 👋</h1>
            <p class="page-subtitle"><?= $welcomeMessage; ?></p>
        </header>

        <?php include __DIR__ . '/partials/flash.php'; ?>

        <section class="summary-grid" aria-label="Özet kartları">
            <?php foreach ($summaryCards as $card): ?>
                <article class="summary-card">
                    <span class="summary-card__label"><?= $escape($card['title']); ?></span>
                    <h2 class="summary-card__value"><?= $escape($card['value']); ?></h2>
                    <p class="summary-card__description"><?= $escape($card['description']); ?></p>
                </article>
            <?php endforeach; ?>
        </section>

        <div class="grid-two-columns">
            <section class="panel" aria-label="Hızlı aksiyonlar">
                <h2>Hızlı Aksiyonlar</h2>
                <div class="quick-actions">
                    <?php foreach ($quickActions as $action): ?>
                        <article class="quick-action">
                            <a href="<?= $escape($action['href']); ?>"><?= $escape($action['label']); ?></a>
                            <p><?= $escape($action['description']); ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="panel" aria-label="Profil özeti">
                <h2>Profil Özeti</h2>
                <div class="profile-summary">
                    <div class="profile-summary__header">
                        <div class="profile-summary__avatar" aria-hidden="true">
                            <?php
                            $avatarBasis = $fullName !== 'Misafir Kullanıcı' ? $fullName : ($username !== '' ? $username : $email);
                            $initials = 'NK';

                            if ($avatarBasis !== '') {
                                $parts = preg_split('/\s+/u', $avatarBasis) ?: [];
                                $letters = array_map(static fn(string $part): string => mb_strtoupper(mb_substr($part, 0, 1, 'UTF-8'), 'UTF-8'), $parts);
                                $letters = array_filter($letters);
                                if ($letters !== []) {
                                    $initials = implode('', array_slice($letters, 0, 2));
                                }
                            }
                            echo $escape($initials);
                            ?>
                        </div>
                        <div>
                            <p class="profile-summary__name"><?= $escape($fullName); ?></p>
                            <?php if ($username !== ''): ?>
                                <p class="profile-summary__meta">@<?= $escape($username); ?></p>
                            <?php elseif ($email !== ''): ?>
                                <p class="profile-summary__meta"><?= $escape($email); ?></p>
                            <?php else: ?>
                                <p class="profile-summary__meta">Profil bilgilerinizi tamamlayın.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($profileDetails): ?>
                        <div class="profile-details">
                            <?php foreach ($profileDetails as $label => $value): ?>
                                <div class="profile-detail">
                                    <span><?= $escape((string)$label); ?></span>
                                    <span><?= $escape((string)$value); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <section class="panel" aria-label="Son aktiviteler">
            <h2>Son Aktiviteler</h2>
            <div class="activity-feed">
                <?php foreach ($activityFeed as $activity): ?>
                    <article class="activity-item">
                        <span class="activity-item__time"><?= $escape($activity['time']); ?></span>
                        <div>
                            <h3 class="activity-item__title"><?= $escape($activity['title']); ?></h3>
                            <p class="activity-item__description"><?= $escape($activity['description']); ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
</div>

<?php if ($sidebarAssets['scripts'] !== ''): ?>
    <?= $sidebarAssets['scripts']; ?>
<?php endif; ?>
</body>
</html>
