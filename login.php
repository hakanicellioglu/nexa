<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

redirect_if_logged_in();

$errors = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $token = $_POST['csrf_token'] ?? '';

    if (!validate_csrf_token($token)) {
        $errors[] = 'Oturum doğrulaması başarısız oldu. Lütfen sayfayı yenileyin.';
    }

    if ($username === '' || $password === '') {
        $errors[] = 'Kullanıcı adı/e-posta ve şifre zorunludur.';
    }

    if (!$errors) {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare('SELECT id, firstname, lastname, email, username, password FROM users WHERE username = :username OR email = :username LIMIT 1');
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, (string) $user['password'])) {
            $errors[] = 'Geçersiz kullanıcı bilgileri.';
        } else {
            unset($user['password']);
            $_SESSION['user'] = $user;
            header('Location: /dashboard.php');
            exit;
        }
    }
}

$pageTitle = 'Giriş Yap - Nexa';
include __DIR__ . '/header.php';
?>
<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h1 class="h3 fw-semibold mb-3 text-center">Giriş Yap</h1>
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
                        <div class="mb-3">
                            <label class="form-label" for="username">Kullanıcı adı veya e-posta</label>
                            <input class="form-control" type="text" id="username" name="username" value="<?= e($username) ?>" required autofocus>
                        </div>
                        <div class="mb-4">
                            <label class="form-label" for="password">Şifre</label>
                            <input class="form-control" type="password" id="password" name="password" required>
                        </div>
                        <button class="btn btn-primary w-100" type="submit">Giriş Yap</button>
                    </form>
                    <p class="text-center small text-muted mt-3 mb-0">Hesabınız yok mu? <a href="/register.php">Kayıt olun</a>.</p>
                </div>
            </div>
        </div>
    </div>
</main>
</body>
</html>
