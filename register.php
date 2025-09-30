<?php
session_start();

require_once __DIR__ . '/config.php';

$errors = [];
$firstname = '';
$lastname = '';
$email = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($firstname === '' || $lastname === '' || $email === '' || $username === '' || $password === '') {
        $errors[] = 'Lütfen tüm alanları doldurun.';
    }

    if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Lütfen geçerli bir e-posta adresi girin.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Parolalar eşleşmiyor.';
    }

    if (empty($errors)) {
        $checkStmt = $mysqli->prepare('SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1');
        if ($checkStmt === false) {
            $errors[] = 'Veritabanı hatası: ' . $mysqli->error;
        } else {
            $checkStmt->bind_param('ss', $email, $username);
            $checkStmt->execute();
            $checkStmt->store_result();

            if ($checkStmt->num_rows > 0) {
                $errors[] = 'Bu e-posta veya kullanıcı adı zaten kayıtlı.';
            }

            $checkStmt->close();
        }
    }

    if (empty($errors)) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $insertStmt = $mysqli->prepare('INSERT INTO users (firstname, lastname, email, username, password_hash) VALUES (?, ?, ?, ?, ?)');
        if ($insertStmt === false) {
            $errors[] = 'Veritabanı hatası: ' . $mysqli->error;
        } else {
            $insertStmt->bind_param('sssss', $firstname, $lastname, $email, $username, $passwordHash);

            if ($insertStmt->execute()) {
                header('Location: login.php');
                exit;
            }

            $errors[] = 'Kayıt sırasında bir sorun oluştu. Lütfen tekrar deneyin.';
            $insertStmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f3f4f6; margin: 0; padding: 0; }
        .container { max-width: 420px; margin: 60px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1); }
        h1 { text-align: center; margin-bottom: 24px; }
        label { display: block; margin-bottom: 6px; font-weight: bold; }
        input[type="text"],
        input[type="email"],
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
        <h1>Kayıt Ol</h1>

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
            <label for="firstname">Ad</label>
            <input type="text" id="firstname" name="firstname" value="<?php echo htmlspecialchars($firstname ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>

            <label for="lastname">Soyad</label>
            <input type="text" id="lastname" name="lastname" value="<?php echo htmlspecialchars($lastname ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>

            <label for="email">E-posta</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>

            <label for="username">Kullanıcı Adı</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>

            <label for="password">Parola</label>
            <input type="password" id="password" name="password" required>

            <label for="confirm_password">Parola (Tekrar)</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <button type="submit">Kayıt Ol</button>
        </form>

        <div class="link">
            <p>Zaten hesabınız var mı? <a href="login.php">Giriş Yap</a></p>
        </div>
    </div>
</body>
</html>
