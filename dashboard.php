<?php
// Dashboard page for Nexa platform
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

        .stat-trend {
            font-size: var(--font-size-sm);
            color: var(--color-success);
            display: flex;
            align-items: center;
            gap: var(--spacing-xs);
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
                <h1 class="dashboard-title">Hoş geldin, Onur!</h1>
                <p class="dashboard-subtitle">Bugünün genel görünümü ve performans metrikleri burada.</p>
            </header>

            <section class="dashboard-grid" aria-label="Performans kartları">
                <article class="stat-card" aria-label="Aktif Projeler">
                    <span class="stat-label">Aktif Projeler</span>
                    <span class="stat-value">18</span>
                    <span class="stat-trend">▲ %4,2 artış</span>
                </article>
                <article class="stat-card" aria-label="Yeni Talepler">
                    <span class="stat-label">Yeni Talepler</span>
                    <span class="stat-value">52</span>
                    <span class="stat-trend">▲ %7,8 artış</span>
                </article>
                <article class="stat-card" aria-label="Müşteri Memnuniyeti">
                    <span class="stat-label">Müşteri Memnuniyeti</span>
                    <span class="stat-value">%96</span>
                    <span class="stat-trend">▲ %1,5 artış</span>
                </article>
            </section>

            <section class="section" aria-labelledby="section-projects">
                <div class="section-header">
                    <h2 id="section-projects" class="section-title">Projeler</h2>
                    <a class="section-action" href="#">Tümünü Gör</a>
                </div>
                <div class="projects-list">
                    <article class="project-item">
                        <div class="project-info">
                            <h3>Finans Otomasyonu</h3>
                            <p class="project-meta">Son güncelleme: 12 dakika önce</p>
                        </div>
                        <span class="project-status">Devam ediyor</span>
                    </article>
                    <article class="project-item">
                        <div class="project-info">
                            <h3>Tedarikçi Yönetimi</h3>
                            <p class="project-meta">Son güncelleme: 1 saat önce</p>
                        </div>
                        <span class="project-status">Analiz</span>
                    </article>
                    <article class="project-item">
                        <div class="project-info">
                            <h3>Ürün Fiyatlandırma</h3>
                            <p class="project-meta">Son güncelleme: 3 saat önce</p>
                        </div>
                        <span class="project-status">Planlama</span>
                    </article>
                </div>
            </section>

            <section class="section" aria-labelledby="section-activity">
                <div class="section-header">
                    <h2 id="section-activity" class="section-title">Son Aktiviteler</h2>
                    <a class="section-action" href="#">Raporları aç</a>
                </div>
                <div class="activity-timeline">
                    <article class="activity-item">
                        <div class="activity-icon" aria-hidden="true">PR</div>
                        <div class="activity-details">
                            <h3>Yeni fiyatlandırma stratejisi eklendi</h3>
                            <span class="activity-meta">30 dakika önce • Onur Aydın</span>
                        </div>
                    </article>
                    <article class="activity-item">
                        <div class="activity-icon" aria-hidden="true">PO</div>
                        <div class="activity-details">
                            <h3>35 adet sipariş onaylandı</h3>
                            <span class="activity-meta">1 saat önce • Satınalma</span>
                        </div>
                    </article>
                    <article class="activity-item">
                        <div class="activity-icon" aria-hidden="true">TD</div>
                        <div class="activity-details">
                            <h3>Yeni tedarikçi kaydı oluşturuldu</h3>
                            <span class="activity-meta">2 saat önce • İş Geliştirme</span>
                        </div>
                    </article>
                </div>
            </section>
        </main>
    </div>

    <?php echo $sidebarScript; ?>
</body>
</html>
