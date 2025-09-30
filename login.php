<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

require __DIR__ . '/config.php';

$errors = [];
$usernameOrEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameOrEmail = trim($_POST['username_or_email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($usernameOrEmail === '') {
        $errors['username_or_email'] = 'Lütfen kullanıcı adı veya e-postanızı girin.';
    }

    if ($password === '') {
        $errors['password'] = 'Lütfen şifrenizi girin.';
    }

    if (!$errors) {
        $statement = $pdo->prepare(
            'SELECT id, firstname, lastname, username, password_hash FROM users WHERE email = :email_identifier OR username = :username_identifier LIMIT 1'
        );
        $statement->execute([
            ':email_identifier' => $usernameOrEmail,
            ':username_identifier' => $usernameOrEmail,
        ]);
        $user = $statement->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $errors['credentials'] = 'Kullanıcı adı/e-posta veya şifre hatalı.';
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['firstname'] = $user['firstname'];
            $_SESSION['lastname'] = $user['lastname'];
            $_SESSION['username'] = $user['username'];

            header('Location: dashboard.php');
            exit;
        }
    }
}

$registered = isset($_GET['registered']);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap</title>
    <?php include __DIR__ . '/fonts/monoton.php'; ?>
    <?php include __DIR__ . '/cdn/bootstrap.php'; ?>
    <style>
        body {
            background: linear-gradient(135deg, #6610f2, #d63384);
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
            <div class="col-lg-4 col-md-6">
                <div class="card p-4 p-md-5">
                    <div class="text-center mb-4">
                        <h1 class="brand-title display-5 text-primary">NEXA</h1>
                        <p class="text-muted">Hesabınıza giriş yapın</p>
                    </div>
                    <?php if ($registered): ?>
                        <div class="alert alert-success">
                            Kayıt işlemi başarılı! Şimdi giriş yapabilirsiniz.
                        </div>
                    <?php endif; ?>
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
                        <div class="mb-3">
                            <label class="form-label" for="username_or_email">Kullanıcı Adı veya E-posta</label>
                            <input type="text" class="form-control" id="username_or_email" name="username_or_email" value="<?= htmlspecialchars($usernameOrEmail, ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="password">Şifre</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="remember" disabled>
                                <label class="form-check-label" for="remember">
                                    Beni hatırla (yakında)
                                </label>
                            </div>
                            <a href="#" class="text-decoration-none disabled" tabindex="-1" aria-disabled="true">Şifremi unuttum?</a>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Giriş Yap</button>
                        </div>
                    </form>
                    <p class="mt-4 text-center mb-0">
                        Henüz hesabınız yok mu? <a href="register.php">Kayıt olun</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
