<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

require __DIR__ . '/config.php';

$errors = [];
$values = [
    'firstname' => '',
    'lastname' => '',
    'email' => '',
    'username' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['firstname'] = trim($_POST['firstname'] ?? '');
    $values['lastname'] = trim($_POST['lastname'] ?? '');
    $values['email'] = trim($_POST['email'] ?? '');
    $values['username'] = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    if ($values['firstname'] === '') {
        $errors['firstname'] = 'Lütfen adınızı girin.';
    }

    if ($values['lastname'] === '') {
        $errors['lastname'] = 'Lütfen soyadınızı girin.';
    }

    if ($values['email'] === '' || !filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Geçerli bir e-posta adresi girin.';
    }

    if ($values['username'] === '' || !preg_match('/^[A-Za-z0-9_\.\-]{3,50}$/', $values['username'])) {
        $errors['username'] = 'Kullanıcı adı en az 3 karakter olmalı ve sadece harf, rakam, nokta, tire veya alt tire içermelidir.';
    }

    if ($password === '' || strlen($password) < 8) {
        $errors['password'] = 'Şifre en az 8 karakter olmalıdır.';
    }

    if ($password !== $passwordConfirm) {
        $errors['password_confirm'] = 'Şifreler eşleşmiyor.';
    }

    if (!$errors) {
        $checkStatement = $pdo->prepare('SELECT id FROM users WHERE email = :email OR username = :username LIMIT 1');
        $checkStatement->execute([
            ':email' => $values['email'],
            ':username' => $values['username'],
        ]);

        if ($checkStatement->fetch()) {
            $errors['duplicate'] = 'Bu e-posta veya kullanıcı adı zaten kullanılıyor.';
        }
    }

    if (!$errors) {
        $insertStatement = $pdo->prepare('INSERT INTO users (firstname, lastname, email, username, password_hash) VALUES (:firstname, :lastname, :email, :username, :password_hash)');
        $insertStatement->execute([
            ':firstname' => $values['firstname'],
            ':lastname' => $values['lastname'],
            ':email' => $values['email'],
            ':username' => $values['username'],
            ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        header('Location: login.php?registered=1');
        exit;
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
            background: linear-gradient(135deg, #0d6efd, #6610f2);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
        }

        .brand-title {
            font-family: 'Monoton', cursive;
            letter-spacing: 6px;
        }

        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 1rem 2.5rem rgba(0, 0, 0, 0.15);
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">
                <div class="card p-4 p-md-5">
                    <div class="text-center mb-4">
                        <h1 class="brand-title display-5 text-primary">NEXA</h1>
                        <p class="text-muted">Yeni hesabınızı oluşturun</p>
                    </div>
                    <?php if ($errors): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <form method="post" novalidate>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="firstname">Ad</label>
                                <input type="text" class="form-control" id="firstname" name="firstname" value="<?= htmlspecialchars($values['firstname'], ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="lastname">Soyad</label>
                                <input type="text" class="form-control" id="lastname" name="lastname" value="<?= htmlspecialchars($values['lastname'], ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="email">E-posta</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($values['email'], ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="username">Kullanıcı Adı</label>
                                <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($values['username'], ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="password">Şifre</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="password_confirm">Şifre (Tekrar)</label>
                                <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                            </div>
                        </div>
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">Kayıt Ol</button>
                        </div>
                    </form>
                    <p class="mt-4 text-center mb-0">
                        Zaten hesabınız var mı? <a href="login.php">Giriş yapın</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
