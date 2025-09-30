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
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f9fafb; color: #111827; }
        header { background: #1f2937; color: #f9fafb; padding: 16px 24px; display: flex; align-items: center; justify-content: space-between; gap: 24px; }
        header h1 { margin: 0; font-size: 1.5rem; white-space: nowrap; }
        .header-actions { display: flex; align-items: center; gap: 16px; }
        nav { display: flex; gap: 16px; }
        nav a { color: #f9fafb; text-decoration: none; font-weight: 600; }
        nav a:hover { text-decoration: underline; }
        .user-info { font-size: 0.95rem; white-space: nowrap; }
        main { padding: 32px 24px; max-width: 960px; margin: 0 auto; }
        a.button { display: inline-block; margin-top: 16px; padding: 10px 18px; background: #2563eb; color: #fff; text-decoration: none; border-radius: 6px; transition: background 0.2s ease; }
        a.button:hover { background: #1d4ed8; }
        .card { background: #fff; border-radius: 8px; padding: 24px; box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08); }
    </style>
</head>
<body>
    <header>
        <h1><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></h1>
        <div class="header-actions">
            <nav>
                <a href="index.php">Ana Sayfa</a>
                <?php if ($isAuthenticated) : ?>
                    <a href="dashboard.php">Kontrol Paneli</a>
                <?php else : ?>
                    <a href="login.php">Giriş Yap</a>
                    <a href="register.php">Kayıt Ol</a>
                <?php endif; ?>
            </nav>
            <?php if ($userFullName !== '') : ?>
                <div class="user-info">Merhaba, <?php echo htmlspecialchars($userFullName, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
        </div>
    </header>
    <main>
