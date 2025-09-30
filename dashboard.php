<?php
// Dashboard page for Nexa platform
session_start();

require __DIR__ . '/config.php';

ob_start();
include __DIR__ . '/sidebar.php';
$sidebarOutput = ob_get_clean();

$sidebarContent = '';
$sidebarStyles = '';
$sidebarScript = '';

if (preg_match('/<aside\b.*<\/aside>/s', $sidebarOutput, $asideMatch)) {
    $sidebarContent = $asideMatch[0];
}

if (preg_match('/<style>(.*?)<\/style>/s', $sidebarOutput, $styleMatch)) {
    $sidebarStyles = trim($styleMatch[1]);
}

if (preg_match('/<script\b.*<\/script>/s', $sidebarOutput, $scriptMatch)) {
    $sidebarScript = $scriptMatch[0];
}

function fetchCount(PDO $pdo, string $table): int
{
    $sanitizedTable = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
    $statement = $pdo->query(sprintf('SELECT COUNT(*) AS total FROM `%s`', $sanitizedTable));

    if ($statement === false) {
        return 0;
    }

    $result = $statement->fetch();

    return (int) ($result['total'] ?? 0);
}

function resolveUserName(PDO $pdo): string
{
    if (!empty($_SESSION['user'])) {
        if (is_array($_SESSION['user'])) {
            $firstname = $_SESSION['user']['firstname'] ?? '';
            $lastname = $_SESSION['user']['lastname'] ?? '';
            $fullName = trim($firstname . ' ' . $lastname);

            if ($fullName !== '') {
                return $fullName;
            }
        } elseif (is_string($_SESSION['user'])) {
            return $_SESSION['user'];
        }
    }

    if (!empty($_SESSION['user_id'])) {
        $statement = $pdo->prepare('SELECT firstname, lastname FROM users WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $_SESSION['user_id']]);
        $user = $statement->fetch();

        if ($user) {
            $fullName = trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? ''));

            if ($fullName !== '') {
                return $fullName;
            }
        }
    }

    return 'Nexa kullanıcısı';
}

function formatRelativeTime(?string $timestamp): string
{
    if (empty($timestamp)) {
        return 'bilinmiyor';
    }

    try {
        $date = new DateTimeImmutable($timestamp);
    } catch (Exception $exception) {
        return $timestamp;
    }

    $now = new DateTimeImmutable('now');
    $diff = $now->getTimestamp() - $date->getTimestamp();

    if ($diff <= 0) {
        return $date->format('d.m.Y H:i');
    }

    $minutes = (int) floor($diff / 60);
    $hours = (int) floor($minutes / 60);
    $days = (int) floor($hours / 24);
    $months = (int) floor($days / 30);
    $years = (int) floor($days / 365);

    if ($years > 0) {
        return $years . ' yıl önce';
    }

    if ($months > 0) {
        return $months . ' ay önce';
    }

    if ($days > 0) {
        return $days . ' gün önce';
    }

    if ($hours > 0) {
        return $hours . ' saat önce';
    }

    if ($minutes > 0) {
        return $minutes . ' dakika önce';
    }

    return 'az önce';
}

function abbreviateLabel(string $label): string
{
    if (function_exists('mb_substr')) {
        return mb_strtoupper(mb_substr($label, 0, 2));
    }

    return strtoupper(substr($label, 0, 2));
}

function resolveTableLabel(string $tableName): string
{
    $map = [
        'orders' => 'Sipariş',
        'projects' => 'Proje',
        'products' => 'Ürün',
        'suppliers' => 'Tedarikçi',
        'users' => 'Kullanıcı',
    ];

    return $map[strtolower($tableName)] ?? ucfirst($tableName);
}

$userName = resolveUserName($pdo);

$statistics = [
    [
        'label' => 'Projeler',
        'value' => fetchCount($pdo, 'projects'),
        'description' => 'Kayıtlı proje sayısı',
    ],
    [
        'label' => 'Siparişler',
        'value' => fetchCount($pdo, 'orders'),
        'description' => 'Verilen siparişlerin toplamı',
    ],
    [
        'label' => 'Tedarikçiler',
        'value' => fetchCount($pdo, 'suppliers'),
        'description' => 'Çalışılan tedarikçi sayısı',
    ],
];

$projectsStatement = $pdo->query(
    'SELECT p.id, p.name, p.created_at, p.updated_at, COUNT(o.id) AS order_count
     FROM projects p
     LEFT JOIN orders o ON o.project_id = p.id
     GROUP BY p.id
     ORDER BY COALESCE(p.updated_at, p.created_at) DESC
     LIMIT 5'
);

$projects = $projectsStatement ? $projectsStatement->fetchAll() : [];

$activitiesStatement = $pdo->query(
    'SELECT l.table_name, l.action_type, l.date, l.new_value, l.old_value, u.firstname, u.lastname
     FROM logs l
     LEFT JOIN users u ON u.id = l.user_id
     ORDER BY l.date DESC
     LIMIT 5'
);

$activities = $activitiesStatement ? $activitiesStatement->fetchAll() : [];
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

        <?php echo $sidebarStyles; ?>

        body {
            margin: 0;
            background-color: var(--background-primary);
            color: var(--text-primary);
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .dashboard-layout {
            display: flex;
            min-height: 100vh;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.02), rgba(79, 70, 229, 0.04));
        }

        .dashboard-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: var(--spacing-xl);
            padding: var(--spacing-2xl);
            background-color: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(12px);
        }

        .dashboard-header {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-sm);
        }

        .dashboard-title {
            font-size: var(--font-size-3xl);
            font-weight: var(--font-weight-semibold);
            color: var(--color-secondary-dark);
        }

        .dashboard-subtitle {
            color: var(--text-secondary);
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: var(--spacing-lg);
        }

        .stat-card {
            padding: var(--spacing-xl);
            background-color: var(--surface-primary);
            border-radius: var(--radius-xl);
            border: 1px solid rgba(15, 23, 42, 0.06);
            box-shadow: var(--shadow-md);
            display: flex;
            flex-direction: column;
            gap: var(--spacing-sm);
        }

        .stat-label {
            font-size: var(--font-size-sm);
            color: var(--text-secondary);
        }

        .stat-value {
            font-size: var(--font-size-2xl);
            font-weight: var(--font-weight-semibold);
        }

        .stat-description {
            font-size: var(--font-size-sm);
            color: var(--text-secondary);
        }

        .section {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-md);
            background-color: var(--surface-primary);
            border-radius: var(--radius-xl);
            border: 1px solid rgba(15, 23, 42, 0.06);
            box-shadow: var(--shadow-md);
            padding: var(--spacing-xl);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-title {
            font-size: var(--font-size-xl);
            font-weight: var(--font-weight-semibold);
        }

        .section-action {
            color: var(--color-secondary);
            text-decoration: none;
            font-weight: var(--font-weight-medium);
        }

        .projects-list {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-lg);
        }

        .project-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .project-info {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-xs);
        }

        .project-status {
            padding: var(--spacing-2xs) var(--spacing-sm);
            border-radius: var(--radius-lg);
            font-size: var(--font-size-sm);
            background: rgba(34, 197, 94, 0.12);
            color: var(--color-success);
        }

        .activity-timeline {
            display: grid;
            gap: var(--spacing-md);
        }

        .activity-item {
            display: flex;
            gap: var(--spacing-md);
            align-items: flex-start;
        }

        .activity-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            background: rgba(79, 70, 229, 0.12);
            color: var(--color-secondary);
            font-weight: var(--font-weight-semibold);
        }

        .activity-details {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-2xs);
        }

        .activity-meta {
            font-size: var(--font-size-sm);
            color: var(--text-secondary);
        }

        @media (max-width: 1024px) {
            .dashboard-layout {
                flex-direction: column;
            }

            .dashboard-content {
                padding: var(--spacing-xl);
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <?php echo $sidebarContent; ?>
        <main class="dashboard-content" role="main">
            <header class="dashboard-header">
                <h1 class="dashboard-title">Hoş geldin, <?php echo htmlspecialchars($userName, ENT_QUOTES, 'UTF-8'); ?>!</h1>
                <p class="dashboard-subtitle">Verileriniz anlık olarak güncellenmektedir.</p>
            </header>

            <section class="dashboard-grid" aria-label="Performans kartları">
                <?php foreach ($statistics as $statistic): ?>
                    <article class="stat-card" aria-label="<?php echo htmlspecialchars($statistic['label'], ENT_QUOTES, 'UTF-8'); ?>">
                        <span class="stat-label"><?php echo htmlspecialchars($statistic['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="stat-value"><?php echo number_format((int) $statistic['value'], 0, ',', '.'); ?></span>
                        <span class="stat-description"><?php echo htmlspecialchars($statistic['description'], ENT_QUOTES, 'UTF-8'); ?></span>
                    </article>
                <?php endforeach; ?>
            </section>

            <section class="section" aria-labelledby="section-projects">
                <div class="section-header">
                    <h2 id="section-projects" class="section-title">Projeler</h2>
                </div>
                <div class="projects-list">
                    <?php if (!empty($projects)): ?>
                        <?php foreach ($projects as $project): ?>
                            <article class="project-item">
                                <div class="project-info">
                                    <h3><?php echo htmlspecialchars($project['name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                    <p class="project-meta">Son güncelleme: <?php echo htmlspecialchars(formatRelativeTime($project['updated_at'] ?: $project['created_at']), ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                                <span class="project-status"><?php echo number_format((int) $project['order_count'], 0, ',', '.'); ?> sipariş</span>
                            </article>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Henüz proje bulunmuyor.</p>
                    <?php endif; ?>
                </div>
            </section>

            <section class="section" aria-labelledby="section-activity">
                <div class="section-header">
                    <h2 id="section-activity" class="section-title">Son Aktiviteler</h2>
                </div>
                <div class="activity-timeline">
                    <?php if (!empty($activities)): ?>
                        <?php foreach ($activities as $activity): ?>
                            <?php
                            $tableLabel = resolveTableLabel((string) ($activity['table_name'] ?? 'Kayıt'));
                            $actionType = strtoupper((string) ($activity['action_type'] ?? ''));
                            $actionMap = [
                                'INSERT' => 'oluşturuldu',
                                'UPDATE' => 'güncellendi',
                                'DELETE' => 'silindi',
                            ];
                            $actionLabel = $actionMap[$actionType] ?? 'işlendi';
                            $actorName = trim(($activity['firstname'] ?? '') . ' ' . ($activity['lastname'] ?? ''));
                            $actorText = $actorName !== '' ? $actorName : 'Sistem';
                            ?>
                            <article class="activity-item">
                                <div class="activity-icon" aria-hidden="true"><?php echo htmlspecialchars(abbreviateLabel($tableLabel), ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="activity-details">
                                    <h3><?php echo htmlspecialchars($tableLabel . ' ' . $actionLabel, ENT_QUOTES, 'UTF-8'); ?></h3>
                                    <span class="activity-meta"><?php echo htmlspecialchars(formatRelativeTime($activity['date'] ?? null), ENT_QUOTES, 'UTF-8'); ?> • <?php echo htmlspecialchars($actorText, ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Herhangi bir aktivite kaydı bulunamadı.</p>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>

    <?php echo $sidebarScript; ?>
</body>
</html>
