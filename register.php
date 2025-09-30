<?php
declare(strict_types=1);

session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

require_once __DIR__ . '/config.php';

$input = [
    'firstname' => '',
    'lastname' => '',
    'email' => '',
    'username' => '',
];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input['firstname'] = trim($_POST['firstname'] ?? '');
    $input['lastname'] = trim($_POST['lastname'] ?? '');
    $input['email'] = trim($_POST['email'] ?? '');
    $input['username'] = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($input['firstname'] === '') {
        $errors[] = 'Lütfen adınızı giriniz.';
    }

    if ($input['lastname'] === '') {
        $errors[] = 'Lütfen soyadınızı giriniz.';
    }

    if ($input['email'] === '' || !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Geçerli bir e-posta adresi giriniz.';
    }

    if ($input['username'] === '' || !preg_match('/^[A-Za-z0-9_]{3,}$/', $input['username'])) {
        $errors[] = 'Kullanıcı adı en az 3 karakter olmalı ve sadece harf, rakam ve alt çizgi içermelidir.';
    }

    if ($password === '' || strlen($password) < 8) {
        $errors[] = 'Şifre en az 8 karakter olmalıdır.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Şifre ve şifre tekrarı eşleşmiyor.';
    }

    if (!$errors) {
        try {
            $emailStatement = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = :email');
            $emailStatement->execute(['email' => $input['email']]);

            if ((int) $emailStatement->fetchColumn() > 0) {
                $errors[] = 'Bu e-posta adresi zaten kayıtlı.';
            }

            $usernameStatement = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = :username');
            $usernameStatement->execute(['username' => $input['username']]);

            if ((int) $usernameStatement->fetchColumn() > 0) {
                $errors[] = 'Bu kullanıcı adı zaten kullanılıyor.';
            }

            if (!$errors) {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                $insertStatement = $pdo->prepare(
                    'INSERT INTO users (firstname, lastname, email, username, password_hash) VALUES (:firstname, :lastname, :email, :username, :password_hash)'
                );

                $insertStatement->execute([
                    'firstname' => $input['firstname'],
                    'lastname' => $input['lastname'],
                    'email' => $input['email'],
                    'username' => $input['username'],
                    'password_hash' => $passwordHash,
                ]);

                $_SESSION['user_id'] = (int) $pdo->lastInsertId();
                $_SESSION['username'] = $input['username'];
                $_SESSION['fullname'] = $input['firstname'] . ' ' . $input['lastname'];

                header('Location: dashboard.php');
                exit;
            }
        } catch (PDOException $exception) {
            $errors[] = 'Kayıt sırasında bir hata oluştu. Lütfen daha sonra tekrar deneyiniz.';
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
    <?php include __DIR__ . '/fonts/monoton.php'; ?>
    <?php include __DIR__ . '/cdn/bootstrap.php'; ?>
    <style>
        body {
            background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
            min-height: 100vh;
        }
        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2);
        }
        .brand-title {
            font-family: 'Monoton', cursive;
            letter-spacing: 4px;
            font-size: 2rem;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center p-3">
    <div class="card w-100" style="max-width: 480px;">
        <div class="card-body p-5">
            <h1 class="brand-title text-center text-primary mb-4">Nexa</h1>
            <h2 class="text-center mb-4">Yeni Hesap Oluştur</h2>

            <?php if ($errors): ?>
                <div class="alert alert-danger" role="alert">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" novalidate>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="firstname" class="form-label">Ad</label>
                        <input type="text" class="form-control" id="firstname" name="firstname" value="<?= htmlspecialchars($input['firstname'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="lastname" class="form-label">Soyad</label>
                        <input type="text" class="form-control" id="lastname" name="lastname" value="<?= htmlspecialchars($input['lastname'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="col-12">
                        <label for="email" class="form-label">E-posta</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($input['email'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="col-12">
                        <label for="username" class="form-label">Kullanıcı Adı</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($input['username'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="col-12">
                        <label for="password" class="form-label">Şifre</label>
                        <input type="password" class="form-control" id="password" name="password" required minlength="8">
                    </div>
                    <div class="col-12">
                        <label for="confirm_password" class="form-label">Şifre Tekrarı</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="8">
                    </div>
                </div>
                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">Kayıt Ol</button>
                </div>
            </form>

            <p class="mt-4 text-center mb-0">
                Zaten hesabınız var mı? <a href="login.php" class="link-primary fw-semibold">Giriş Yapın</a>
            </p>
        </div>
    </div>
</body>
</html>
