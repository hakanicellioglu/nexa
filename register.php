<?php
// Register page for Nexa platform

declare(strict_types=1);

session_start();

require __DIR__ . '/config.php';

$errors = [];
$formData = [
    'first_name' => '',
    'last_name' => '',
    'username' => '',
    'email' => '',
];
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'first_name' => trim((string)($_POST['first_name'] ?? '')),
        'last_name' => trim((string)($_POST['last_name'] ?? '')),
        'username' => trim((string)($_POST['username'] ?? '')),
        'email' => trim((string)($_POST['email'] ?? '')),
    ];
    $password = (string)($_POST['password'] ?? '');
    $passwordConfirmation = (string)($_POST['password_confirmation'] ?? '');

    if ($formData['first_name'] === '') {
        $errors['first_name'] = 'Ad alanı zorunludur.';
    }

    if ($formData['last_name'] === '') {
        $errors['last_name'] = 'Soyad alanı zorunludur.';
    }

    if ($formData['username'] === '') {
        $errors['username'] = 'Kullanıcı adı zorunludur.';
    } elseif (mb_strlen($formData['username']) < 3) {
        $errors['username'] = 'Kullanıcı adı en az 3 karakter olmalıdır.';
    }

    if ($formData['email'] === '') {
        $errors['email'] = 'E-posta alanı zorunludur.';
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Geçerli bir e-posta adresi girin.';
    }

    if ($password === '') {
        $errors['password'] = 'Şifre alanı zorunludur.';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Şifre en az 8 karakter olmalıdır.';
    }

    if ($passwordConfirmation === '') {
        $errors['password_confirmation'] = 'Şifre tekrarı zorunludur.';
    } elseif ($password !== $passwordConfirmation) {
        $errors['password_confirmation'] = 'Şifreler eşleşmiyor.';
    }

    if (!$errors) {
        $emailCheck = $pdo->prepare('SELECT 1 FROM users WHERE email = :email LIMIT 1');
        $emailCheck->execute(['email' => $formData['email']]);
        if ($emailCheck->fetchColumn()) {
            $errors['email'] = 'Bu e-posta adresiyle kayıtlı bir kullanıcı zaten bulunuyor.';
        }
    }

    if (!$errors) {
        $usernameCheck = $pdo->prepare('SELECT 1 FROM users WHERE username = :username LIMIT 1');
        $usernameCheck->execute(['username' => $formData['username']]);
        if ($usernameCheck->fetchColumn()) {
            $errors['username'] = 'Bu kullanıcı adı zaten kullanılıyor.';
        }
    }

    if (!$errors) {
        $insertUser = $pdo->prepare(
            'INSERT INTO users (firstname, lastname, email, username, password_hash) VALUES (:firstname, :lastname, :email, :username, :password_hash)'
        );
        $insertUser->execute([
            'firstname' => $formData['first_name'],
            'lastname' => $formData['last_name'],
            'email' => $formData['email'],
            'username' => $formData['username'],
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        $successMessage = 'Kayıt işlemi başarıyla tamamlandı. Giriş yapabilirsiniz.';
        $formData = [
            'first_name' => '',
            'last_name' => '',
            'username' => '',
            'email' => '',
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexa - Kayıt Ol</title>
    <?php include __DIR__ . '/fonts/monoton.php'; ?>
    <style>
        <?php include __DIR__ . '/assets/css/root.css'; ?>

        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%);
        }

        .auth-card {
            width: min(420px, 90vw);
            background-color: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(12px);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-xl);
            padding: var(--spacing-2xl) var(--spacing-2xl) var(--spacing-xl);
        }

        .auth-title {
            font-family: 'Monoton', cursive;
            font-size: var(--font-size-4xl);
            text-align: center;
            color: var(--color-primary-dark);
            margin-bottom: var(--spacing-lg);
            letter-spacing: 2px;
        }

        .auth-subtitle {
            text-align: center;
            color: var(--text-secondary);
            margin-bottom: var(--spacing-xl);
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-xs);
            margin-bottom: var(--spacing-lg);
        }

        label {
            font-weight: var(--font-weight-medium);
            color: var(--text-secondary);
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            padding: var(--spacing-sm) var(--spacing-md);
            border: 1px solid var(--border-secondary);
            border-radius: var(--radius-md);
            transition: border-color var(--transition-fast), box-shadow var(--transition-fast);
        }

        input:focus {
            outline: none;
            border-color: var(--border-focus);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        .primary-button {
            width: 100%;
            padding: var(--spacing-sm);
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%);
            color: var(--text-inverse);
            border: none;
            border-radius: var(--radius-md);
            font-weight: var(--font-weight-semibold);
            font-size: var(--font-size-lg);
            transition: transform var(--transition-fast), box-shadow var(--transition-fast);
        }

        .primary-button:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .auth-footer {
            margin-top: var(--spacing-xl);
            text-align: center;
            color: var(--text-secondary);
        }

        .auth-footer a {
            font-weight: var(--font-weight-semibold);
        }

        .alert {
            border-radius: var(--radius-md);
            padding: var(--spacing-sm) var(--spacing-md);
            margin-bottom: var(--spacing-lg);
            font-size: var(--font-size-sm);
        }

        .alert.success {
            background-color: rgba(16, 185, 129, 0.15);
            color: #047857;
            border: 1px solid rgba(16, 185, 129, 0.35);
        }

        .alert.error {
            background-color: rgba(248, 113, 113, 0.15);
            color: #b91c1c;
            border: 1px solid rgba(248, 113, 113, 0.35);
        }

        .alert ul {
            margin: 0;
            padding-left: var(--spacing-lg);
        }
    </style>
</head>
<body>
    <main class="auth-card" role="main">
        <h1 class="auth-title">Nexa</h1>
        <p class="auth-subtitle">Yeni hesabınızı oluşturun</p>
        <?php if ($successMessage !== ''): ?>
            <div class="alert success" role="alert">
                <?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <?php if ($errors): ?>
            <div class="alert error" role="alert">
                <ul>
                    <?php foreach ($errors as $message): ?>
                        <li><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method="post" novalidate>
            <div class="form-group">
                <label for="first_name">Ad</label>
                <input type="text" name="first_name" id="first_name" placeholder="Adınızı girin" value="<?= htmlspecialchars($formData['first_name'], ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div class="form-group">
                <label for="last_name">Soyad</label>
                <input type="text" name="last_name" id="last_name" placeholder="Soyadınızı girin" value="<?= htmlspecialchars($formData['last_name'], ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div class="form-group">
                <label for="username">Kullanıcı Adı</label>
                <input type="text" name="username" id="username" placeholder="Kullanıcı adınızı girin" value="<?= htmlspecialchars($formData['username'], ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">E-posta</label>
                <input type="email" name="email" id="email" placeholder="ornek@nexa.com" value="<?= htmlspecialchars($formData['email'], ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Şifre</label>
                <input type="password" name="password" id="password" placeholder="Şifrenizi girin" required>
            </div>
            <div class="form-group">
                <label for="password_confirmation">Şifre (Tekrar)</label>
                <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Şifrenizi tekrar girin" required>
            </div>
            <button type="submit" class="primary-button">Kayıt Ol</button>
        </form>
        <p class="auth-footer">
            Zaten hesabınız var mı? <a href="login.php">Giriş yapın</a>
        </p>
    </main>
</body>
</html>
