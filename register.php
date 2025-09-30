<?php
// Register page for Nexa platform
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
    </style>
</head>
<body>
    <main class="auth-card" role="main">
        <h1 class="auth-title">Nexa</h1>
        <p class="auth-subtitle">Yeni hesabınızı oluşturun</p>
        <form action="#" method="post" novalidate>
            <div class="form-group">
                <label for="name">Ad Soyad</label>
                <input type="text" name="name" id="name" placeholder="Adınızı girin" required>
            </div>
            <div class="form-group">
                <label for="email">E-posta</label>
                <input type="email" name="email" id="email" placeholder="ornek@nexa.com" required>
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
