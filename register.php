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
include __DIR__ . '/header.php';
?>
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
</body>
</html>
