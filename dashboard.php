<?php
declare(strict_types=1);

session_start();

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/sidebar.php';

$userFullName = $_SESSION['fullname'] ?? '';
$username = $_SESSION['username'] ?? '';

try {
    if ($userFullName === '' || $username === '') {
        $statement = $pdo->prepare('SELECT firstname, lastname, username FROM users WHERE id = :id LIMIT 1');
        $statement->execute(['id' => (int) $_SESSION['user_id']]);
        $user = $statement->fetch();

        if ($user) {
            $userFullName = trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? ''));
            $username = $user['username'] ?? $username;
            $_SESSION['fullname'] = $userFullName;
            $_SESSION['username'] = $username;
        }
    }

    $stats = [
        'projects' => 0,
        'orders' => 0,
        'suppliers' => 0,
    ];

    $stats['projects'] = (int) $pdo->query('SELECT COUNT(*) FROM projects')->fetchColumn();
    $stats['orders'] = (int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
    $stats['suppliers'] = (int) $pdo->query('SELECT COUNT(*) FROM suppliers')->fetchColumn();
} catch (PDOException $exception) {
    $stats = [
        'projects' => 0,
        'orders' => 0,
        'suppliers' => 0,
    ];
}

$sidebarFonts = nexa_sidebar_fonts();
$sidebarHtml = nexa_render_sidebar('dashboard.php');
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gösterge Paneli</title>
    <?= $sidebarFonts; ?>
    <?php include __DIR__ . '/cdn/bootstrap.php'; ?>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar-container {
            width: 260px;
        }
        .welcome-title {
            font-family: 'Monoton', cursive;
            letter-spacing: 3px;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <div class="sidebar-container">
            <?= $sidebarHtml; ?>
        </div>
        <main class="flex-grow-1 p-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                <div>
                    <h1 class="welcome-title text-primary mb-0">Nexa</h1>
                    <p class="text-muted mb-0">Hoş geldiniz<?= $userFullName ? ', ' . htmlspecialchars($userFullName, ENT_QUOTES, 'UTF-8') : ''; ?>!</p>
                    <?php if ($username): ?>
                        <small class="text-secondary">Kullanıcı adınız: <?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></small>
                    <?php endif; ?>
                </div>
                <a class="btn btn-outline-primary" href="?logout=1">Oturumu Kapat</a>
            </div>

            <section>
                <h2 class="h4 mb-3">Özet Bilgiler</h2>
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h3 class="h6 text-uppercase text-muted">Projeler</h3>
                                <p class="display-6 fw-bold mb-0 text-primary"><?= number_format($stats['projects']); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h3 class="h6 text-uppercase text-muted">Siparişler</h3>
                                <p class="display-6 fw-bold mb-0 text-success"><?= number_format($stats['orders']); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h3 class="h6 text-uppercase text-muted">Tedarikçiler</h3>
                                <p class="display-6 fw-bold mb-0 text-danger"><?= number_format($stats['suppliers']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
