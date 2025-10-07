<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$user = require_login();

$pdo = get_db_connection();

// Şirket sayısı
$companyCountStmt = $pdo->prepare('SELECT COUNT(*) FROM company WHERE user_id = :user_id');
$companyCountStmt->execute([':user_id' => $user['id']]);
$companyCount = (int) $companyCountStmt->fetchColumn();

// Toplam iletişim kişisi sayısı
$contactCountStmt = $pdo->query('
    SELECT COUNT(*) 
    FROM company_contacts cc
    INNER JOIN company c ON cc.company_id = c.id
    WHERE c.user_id = ' . (int)$user['id']
);
$contactCount = (int) $contactCountStmt->fetchColumn();

// Son güncellenen şirket bilgisi
$latestCompanyStmt = $pdo->prepare('
    SELECT name, updated_at 
    FROM company 
    WHERE user_id = :user_id 
    ORDER BY updated_at DESC 
    LIMIT 1
');
$latestCompanyStmt->execute([':user_id' => $user['id']]);
$latestCompany = $latestCompanyStmt->fetch();

$pageTitle = 'Panel - Nexa';
$csrfToken = ensure_csrf_token();
$hasSidebar = true;
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= e($csrfToken) ?>">
    <title><?= e($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --warning-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --info-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-size: 0.95rem;
        }
        
        .app-layout {
            min-height: 100vh;
        }
        
        .sidebar {
            background: rgba(17, 24, 39, 0.95);
            backdrop-filter: blur(10px);
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.75);
            font-weight: 500;
            transition: all 0.3s ease;
            border-radius: 10px;
            margin: 4px 0;
        }
        
        .sidebar .nav-link.active,
        .sidebar .nav-link:hover {
            color: #fff;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transform: translateX(5px);
        }
        
        .sidebar .nav-link i {
            width: 1.5rem;
        }
        
        .main-content {
            min-height: 100vh;
            background: #f8f9fc;
            box-shadow: -10px 0 40px rgba(0, 0, 0, 0.1);
        }
        
        .welcome-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            color: white;
            padding: 2.5rem;
            position: relative;
            overflow: hidden;
            margin-bottom: 2rem;
        }
        
        .welcome-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }
        
        .welcome-card::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -10%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
        }

        .welcome-card-content {
            position: relative;
            z-index: 1;
        }

        .welcome-card .h2 {
            font-size: clamp(1.4rem, 2.4vw, 1.9rem);
        }

        .welcome-card p {
            font-size: 0.95rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 1.75rem;
            border: none;
            transition: all 0.3s ease;
            height: 100%;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.2);
        }
        
        .stat-card:hover::before {
            transform: scaleX(1);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .stat-icon.purple {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .stat-icon.green {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }
        
        .stat-icon.pink {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        
        .stat-icon.blue {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }
        
        .stat-value {
            font-size: 2.1rem;
            font-weight: 800;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #6b7280;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-card {
            background: white;
            border-radius: 20px;
            padding: 1.75rem;
            border: none;
            height: 100%;
            transition: all 0.3s ease;
        }
        
        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        
        .info-card-title {
            font-weight: 700;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
            margin-bottom: 1rem;
        }
        
        .badge-custom {
            padding: 0.5rem 1rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.875rem;
        }
        
        .checklist-item {
            padding: 0.75rem 0;
            border-bottom: 1px solid #f3f4f6;
            transition: all 0.2s ease;
        }
        
        .checklist-item:last-child {
            border-bottom: none;
        }
        
        .checklist-item:hover {
            padding-left: 0.5rem;
        }
        
        .checklist-item i {
            font-size: 1.1rem;
        }
        
        .quick-action-btn {
            background: var(--primary-gradient);
            border: none;
            border-radius: 14px;
            padding: 0.875rem 1.75rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .quick-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .activity-item {
            padding: 1rem;
            background: #f9fafb;
            border-radius: 12px;
            margin-bottom: 0.75rem;
            transition: all 0.2s ease;
        }
        
        .activity-item:hover {
            background: #f3f4f6;
            transform: translateX(5px);
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-gradient);
            color: white;
        }
        
        @media (max-width: 991.98px) {
            body {
                background: #f8f9fc;
            }
        }
    </style>
</head>
<body>
<div class="container-fluid app-layout">
    <div class="row flex-nowrap">
        <?php include __DIR__ . '/sidebar.php'; ?>
        <main class="col main-content px-3 px-lg-4 py-4">
            <?php if ($hasSidebar): ?>
                <button class="btn btn-outline-secondary d-lg-none mb-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
                    <i class="bi bi-list me-1"></i> Menü
                </button>
            <?php endif; ?>
            
            <!-- Welcome Card -->
            <div class="welcome-card">
                <div class="welcome-card-content">
                    <div>
                        <h1 class="h2 fw-bold mb-2">Merhaba, <?= e($user['firstname']) ?> 👋</h1>
                        <p class="mb-0 opacity-90">Kontrol panelinize hoş geldiniz. İşletmenizi yönetmek için hazırsınız.</p>
                    </div>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <div class="stat-card">
                        <div class="stat-icon green">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="stat-value"><?= $contactCount ?></div>
                        <div class="stat-label">Şirket Çalışanları</div>
                    </div>
                </div>
            </div>
            
            <!-- Info Cards Row -->
            <div class="row g-4">
                <div class="col-12">
                    <div class="info-card">
                        <h6 class="info-card-title">
                            <i class="bi bi-clock-history me-2 text-info"></i>
                            Son Aktivite
                        </h6>
                        <?php if ($latestCompany): ?>
                        <div class="activity-item">
                            <div class="d-flex align-items-start gap-3">
                                <div class="activity-icon">
                                    <i class="bi bi-building"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold mb-1"><?= e($latestCompany['name']) ?></div>
                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i>
                                        <?= date('d.m.Y H:i', strtotime($latestCompany['updated_at'])) ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <p class="text-muted mb-0">Henüz aktivite bulunmamaktadır.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>