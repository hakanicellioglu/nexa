<?php
session_start();

if (! isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

$firstname = $_SESSION['firstname'] ?? '';
$lastname = $_SESSION['lastname'] ?? '';
$username = $_SESSION['username'] ?? '';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontrol Paneli</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f9fafb; margin: 0; padding: 0; }
        .container { max-width: 640px; margin: 80px auto; background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.1); text-align: center; }
        h1 { margin-bottom: 16px; color: #111827; }
        p { color: #4b5563; margin-bottom: 24px; }
        form { display: inline-block; }
        button { padding: 12px 24px; background: #ef4444; color: #fff; border: none; border-radius: 6px; font-size: 16px; cursor: pointer; }
        button:hover { background: #dc2626; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Hoş geldiniz, <?php echo htmlspecialchars($firstname . ' ' . $lastname, ENT_QUOTES, 'UTF-8'); ?>!</h1>
        <p>Kullanıcı adınız: <strong><?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></strong></p>
        <form method="post" action="">
            <input type="hidden" name="logout" value="1">
            <button type="submit">Çıkış Yap</button>
        </form>
    </div>
</body>
</html>
