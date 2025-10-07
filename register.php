<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

redirect_if_logged_in();

$errors = [];
$form = [
    'firstname' => '',
    'lastname' => '',
    'email' => '',
    'username' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form['firstname'] = trim($_POST['firstname'] ?? '');
    $form['lastname'] = trim($_POST['lastname'] ?? '');
    $form['email'] = trim($_POST['email'] ?? '');
    $form['username'] = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';
    $token = $_POST['csrf_token'] ?? '';

    if (!validate_csrf_token($token)) {
        $errors[] = 'Oturum doğrulaması başarısız oldu. Lütfen sayfayı yenileyin.';
    }

    if ($form['firstname'] === '' || $form['lastname'] === '') {
        $errors[] = 'Ad ve soyad zorunludur.';
    }

    if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Geçerli bir e-posta adresi girin.';
    }

    if ($form['username'] === '') {
        $errors[] = 'Kullanıcı adı zorunludur.';
    }

    if ($password === '' || $passwordConfirm === '') {
        $errors[] = 'Şifre ve şifre doğrulama zorunludur.';
    } elseif ($password !== $passwordConfirm) {
        $errors[] = 'Şifreler birbiriyle uyuşmuyor.';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Şifreniz en az 8 karakter olmalıdır.';
    }

    if (!$errors) {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = :email OR username = :username');
        $stmt->execute([
            ':email' => $form['email'],
            ':username' => $form['username'],
        ]);

        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'Bu e-posta veya kullanıcı adı zaten kullanımda.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO users (firstname, lastname, email, username, password) VALUES (:firstname, :lastname, :email, :username, :password)');
            $stmt->execute([
                ':firstname' => $form['firstname'],
                ':lastname' => $form['lastname'],
                ':email' => $form['email'],
                ':username' => $form['username'],
                ':password' => password_hash($password, PASSWORD_DEFAULT),
            ]);

            $_SESSION['user'] = [
                'id' => (int) $pdo->lastInsertId(),
                'firstname' => $form['firstname'],
                'lastname' => $form['lastname'],
                'email' => $form['email'],
                'username' => $form['username'],
            ];

            header('Location: dashboard.php');
            exit;
        }
    }
}

$pageTitle = 'Kayıt Ol - Nexa';
$csrfToken = ensure_csrf_token();
$user = current_user();
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f5f6fa;
        }
        .sidebar {
            min-height: 100vh;
            background: #111827;
        }
        .sidebar a {
            color: rgba(255, 255, 255, 0.75);
        }
        .sidebar a.active,
        .sidebar a:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg bg-white border-bottom shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="index.php">Nexa</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <?php if ($user === null): ?>
                    <li class="nav-item"><a class="nav-link" href="login.php">Giriş Yap</a></li>
                    <li class="nav-item"><a class="btn btn-primary" href="register.php">Kayıt Ol</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Panel</a></li>
                    <li class="nav-item"><a class="nav-link" href="company.php">Şirket Bilgileri</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i><?= e($user['firstname'] . ' ' . $user['lastname']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><span class="dropdown-item-text text-muted small"><?= e($user['email']) ?></span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Çıkış Yap</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h1 class="h3 fw-semibold mb-3 text-center">Hesap Oluştur</h1>
                    <?php if ($errors): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= e($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <form method="post" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= e(ensure_csrf_token()) ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="firstname">Ad</label>
                                <input class="form-control" type="text" id="firstname" name="firstname" value="<?= e($form['firstname']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="lastname">Soyad</label>
                                <input class="form-control" type="text" id="lastname" name="lastname" value="<?= e($form['lastname']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="email">E-posta</label>
                                <input class="form-control" type="email" id="email" name="email" value="<?= e($form['email']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="username">Kullanıcı adı</label>
                                <input class="form-control" type="text" id="username" name="username" value="<?= e($form['username']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="password">Şifre</label>
                                <input class="form-control" type="password" id="password" name="password" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="password_confirm">Şifre (tekrar)</label>
                                <input class="form-control" type="password" id="password_confirm" name="password_confirm" required>
                            </div>
                        </div>
                        <button class="btn btn-primary w-100 mt-4" type="submit">Kayıt Ol</button>
                    </form>
                    <p class="text-center small text-muted mt-3 mb-0">Zaten hesabınız var mı? <a href="login.php">Giriş yapın</a>.</p>
                </div>
            </div>
        </div>
    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
</body>
</html>
