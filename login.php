<?php
session_start();

require_once __DIR__ . '/config.php';

$errors = [];
$identifier = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($identifier === '' || $password === '') {
        $errors[] = 'Lütfen kullanıcı adı/e-posta ve parolanızı girin.';
    }

    if (empty($errors)) {
        $stmt = $mysqli->prepare('SELECT id, firstname, lastname, username, password_hash FROM users WHERE username = ? OR email = ? LIMIT 1');

        if ($stmt === false) {
            $errors[] = 'Veritabanı hatası: ' . $mysqli->error;
        } else {
            $stmt->bind_param('ss', $identifier, $identifier);
            if ($stmt->execute()) {
                $stmt->bind_result($userId, $firstNameDb, $lastNameDb, $usernameDb, $passwordHash);

                if ($stmt->fetch() && password_verify($password, $passwordHash)) {
                    $_SESSION['user_id'] = $userId;
                    $_SESSION['username'] = $usernameDb;
                    $_SESSION['firstname'] = $firstNameDb;
                    $_SESSION['lastname'] = $lastNameDb;
                    $stmt->close();

                    header('Location: index.php');
                    exit;
                }

                $errors[] = 'Geçersiz giriş bilgileri. Lütfen tekrar deneyin.';
            } else {
                $errors[] = 'Giriş yapılırken bir hata oluştu. Lütfen tekrar deneyin.';
            }

            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap</title>
    <style>
        body { font-family: Arial, sans-serif; background: #eef2ff; margin: 0; padding: 0; }
        .container { max-width: 420px; margin: 60px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1); }
        h1 { text-align: center; margin-bottom: 24px; }
        label { display: block; margin-bottom: 6px; font-weight: bold; }
        input[type="text"],
        input[type="password"] { width: 100%; padding: 10px; margin-bottom: 16px; border: 1px solid #d1d5db; border-radius: 4px; }
        button { width: 100%; padding: 12px; background: #2563eb; color: #fff; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; }
        button:hover { background: #1d4ed8; }
        .message { margin-bottom: 16px; padding: 12px; border-radius: 4px; }
        .error { background: #fee2e2; color: #b91c1c; }
        .link { text-align: center; margin-top: 16px; }
        .link a { color: #2563eb; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Giriş Yap</h1>

        <?php if (! empty($errors)) : ?>
            <div class="message error">
                <ul>
                    <?php foreach ($errors as $error) : ?>
                        <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="">
            <label for="identifier">Kullanıcı Adı veya E-posta</label>
            <input type="text" id="identifier" name="identifier" value="<?php echo htmlspecialchars($identifier ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>

            <label for="password">Parola</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Giriş Yap</button>
        </form>

        <div class="link">
            <p>Hesabınız yok mu? <a href="register.php">Kayıt Ol</a></p>
        </div>
    </div>
</body>
</html>
