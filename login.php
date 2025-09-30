<?php
// Login page for Nexa platform
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexa - Giriş Yap</title>
    <?php include __DIR__ . '/fonts/monoton.php'; ?>
    <style>
        <?php include __DIR__ . '/assets/css/root.css'; ?>

        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, var(--color-secondary) 0%, var(--color-primary) 100%);
        }

        .auth-card {
            width: min(400px, 90vw);
            background-color: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(10px);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-xl);
            padding: var(--spacing-2xl) var(--spacing-2xl) var(--spacing-xl);
        }

        .auth-title {
            font-family: 'Monoton', cursive;
            font-size: var(--font-size-4xl);
            text-align: center;
            color: var(--color-secondary-dark);
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
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.25);
        }

        .primary-button {
            width: 100%;
            padding: var(--spacing-sm);
            background: linear-gradient(135deg, var(--color-secondary) 0%, var(--color-primary) 100%);
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

        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: var(--spacing-lg);
        }

        .form-options label {
            display: flex;
            align-items: center;
            gap: var(--spacing-xs);
            font-size: var(--font-size-sm);
        }

        .form-options a {
            font-size: var(--font-size-sm);
        }
    </style>
</head>
<body>
    <main class="auth-card" role="main">
        <h1 class="auth-title">Nexa</h1>
        <p class="auth-subtitle">Hesabınıza giriş yapın</p>
        <form action="#" method="post" novalidate>
            <div class="form-group">
                <label for="email">E-posta</label>
                <input type="email" name="email" id="email" placeholder="ornek@nexa.com" required>
            </div>
            <div class="form-group">
                <label for="password">Şifre</label>
                <input type="password" name="password" id="password" placeholder="Şifrenizi girin" required>
            </div>
            <div class="form-options">
                <label>
                    <input type="checkbox" name="remember" value="1">
                    Beni hatırla
                </label>
                <a href="#">Şifremi unuttum</a>
            </div>
            <button type="submit" class="primary-button">Giriş Yap</button>
        </form>
        <p class="auth-footer">
            Hesabınız yok mu? <a href="register.php">Hemen kaydolun</a>
        </p>
    </main>
</body>
</html>
