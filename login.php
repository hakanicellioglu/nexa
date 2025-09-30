<?php
declare(strict_types=1);

session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

require_once __DIR__ . '/config.php';

$identifier = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($identifier === '') {
        $errors[] = 'Lütfen kullanıcı adı veya e-posta giriniz.';
    }

    if ($password === '') {
        $errors[] = 'Lütfen şifrenizi giriniz.';
    }

    if (!$errors) {
        try {
            $statement = $pdo->prepare('SELECT id, firstname, lastname, username, password_hash FROM users WHERE username = :identifier OR email = :identifier LIMIT 1');
            $statement->execute(['identifier' => $identifier]);
            $user = $statement->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = (int) $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['fullname'] = trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? ''));

                header('Location: dashboard.php');
                exit;
            }

            $errors[] = 'Geçersiz kullanıcı adı/e-posta veya şifre.';
        } catch (PDOException $exception) {
            $errors[] = 'Giriş sırasında bir hata oluştu. Lütfen daha sonra tekrar deneyiniz.';
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
    <?php include __DIR__ . '/fonts/monoton.php'; ?>
    <?php include __DIR__ . '/cdn/bootstrap.php'; ?>
    <style>
        body {
            background: linear-gradient(135deg, #6610f2 0%, #d63384 100%);
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
    <div class="card w-100" style="max-width: 420px;">
        <div class="card-body p-5">
            <h1 class="brand-title text-center text-primary mb-4">Nexa</h1>
            <h2 class="text-center mb-4">Hesabınıza Giriş Yapın</h2>

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
                <div class="mb-3">
                    <label for="identifier" class="form-label">Kullanıcı Adı veya E-posta</label>
                    <input type="text" class="form-control" id="identifier" name="identifier" value="<?= htmlspecialchars($identifier, ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Şifre</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">Giriş Yap</button>
                </div>
            </form>

            <p class="mt-4 text-center mb-0">
                Henüz hesabınız yok mu? <a href="register.php" class="link-primary fw-semibold">Kayıt Olun</a>
            </p>
        </div>
    </div>
</body>
</html>
