<?php
// Login page for Nexa platform

declare(strict_types=1);

session_start();

if (!empty($_SESSION['user']) || !empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

require __DIR__ . '/config.php';

$errors = [];
$generalError = '';
$formData = [
    'email' => '',
    'remember' => false,
];
$isJsonRequest = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acceptHeader = (string)($_SERVER['HTTP_ACCEPT'] ?? '');
    $requestedWith = strtolower((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''));
    $isJsonRequest = strpos($acceptHeader, 'application/json') !== false || $requestedWith === 'xmlhttprequest';

    $formData['email'] = trim((string)($_POST['email'] ?? ''));
    $formData['remember'] = isset($_POST['remember']);
    $password = (string)($_POST['password'] ?? '');

    if ($formData['email'] === '') {
        $errors['email'] = 'E-posta alanı zorunludur.';
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Geçerli bir e-posta adresi girin.';
    }

    if ($password === '') {
        $errors['password'] = 'Şifre alanı zorunludur.';
    }

    if (!$errors) {
        $userQuery = $pdo->prepare(
            'SELECT id, firstname, lastname, email, username, password_hash FROM users WHERE email = :email LIMIT 1'
        );
        $userQuery->execute(['email' => $formData['email']]);
        $user = $userQuery->fetch();

        if ($user && password_verify($password, (string)$user['password_hash'])) {
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['user'] = [
                'id' => (int)$user['id'],
                'firstname' => (string)$user['firstname'],
                'lastname' => (string)$user['lastname'],
                'email' => (string)$user['email'],
                'username' => (string)$user['username'],
            ];

            if (!empty($_SESSION['flash'])) {
                $_SESSION['flash'] = (array)$_SESSION['flash'];
            } else {
                $_SESSION['flash'] = [];
            }

            $_SESSION['flash'][] = 'Başarıyla giriş yaptınız.';

            if ($isJsonRequest) {
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode([
                    'success' => true,
                    'message' => 'Başarıyla giriş yaptınız.',
                    'redirect' => 'dashboard.php',
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            header('Location: dashboard.php');
            exit;
        }

        $generalError = 'E-posta veya şifre hatalı.';
    }

    if ($isJsonRequest) {
        header('Content-Type: application/json; charset=UTF-8');

        if ($errors) {
            http_response_code(422);
            echo json_encode([
                'success' => false,
                'errors' => $errors,
            ], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => $generalError !== '' ? $generalError : 'Giriş işlemi tamamlanamadı.',
            ], JSON_UNESCAPED_UNICODE);
        }

        exit;
    }
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
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

        .alert {
            border-radius: var(--radius-md);
            padding: var(--spacing-sm) var(--spacing-md);
            margin-bottom: var(--spacing-lg);
            font-size: var(--font-size-sm);
        }

        .alert-error {
            background-color: rgba(239, 68, 68, 0.1);
            color: #b91c1c;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .field-error {
            color: #b91c1c;
            font-size: var(--font-size-sm);
        }
    </style>
</head>
<body>
    <main class="auth-card" role="main">
        <h1 class="auth-title">Nexa</h1>
        <p class="auth-subtitle">Hesabınıza giriş yapın</p>
        <div id="general-error" class="alert alert-error" role="alert" <?php echo $generalError === '' ? 'hidden' : ''; ?>>
            <span data-message><?php echo e($generalError); ?></span>
        </div>
        <form action="" method="post" novalidate data-async="true">
            <div class="form-group">
                <label for="email">E-posta</label>
                <input type="email" name="email" id="email" placeholder="ornek@nexa.com" value="<?php echo e($formData['email']); ?>" required>
                <p class="field-error" data-error-for="email" <?php echo isset($errors['email']) ? '' : 'hidden'; ?>><?php echo isset($errors['email']) ? e($errors['email']) : ''; ?></p>
            </div>
            <div class="form-group">
                <label for="password">Şifre</label>
                <input type="password" name="password" id="password" placeholder="Şifrenizi girin" required>
                <p class="field-error" data-error-for="password" <?php echo isset($errors['password']) ? '' : 'hidden'; ?>><?php echo isset($errors['password']) ? e($errors['password']) : ''; ?></p>
            </div>
            <div class="form-options">
                <label>
                    <input type="checkbox" name="remember" value="1" <?php echo $formData['remember'] ? 'checked' : ''; ?>>
                    Beni hatırla
                </label>
                <a href="#">Şifremi unuttum</a>
            </div>
            <button type="submit" class="primary-button">Giriş Yap</button>
        </form>
        <p class="auth-footer">
            Hesabınız yok mu? <a href="register.php">Hemen kaydolun</a>
        </p>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('form[data-async="true"]');

            if (!form || typeof window.fetch !== 'function') {
                return;
            }

            const submitButton = form.querySelector('button[type="submit"]');
            const generalError = document.getElementById('general-error');
            const generalErrorMessage = generalError ? generalError.querySelector('[data-message]') || generalError : null;
            const fieldErrors = {};

            form.querySelectorAll('[data-error-for]').forEach((element) => {
                const fieldName = element.getAttribute('data-error-for');

                if (fieldName) {
                    fieldErrors[fieldName] = element;
                }
            });

            form.addEventListener('submit', async (event) => {
                event.preventDefault();

                Object.values(fieldErrors).forEach((element) => {
                    element.textContent = '';
                    element.hidden = true;
                });

                if (generalError && generalErrorMessage) {
                    generalErrorMessage.textContent = '';
                    generalError.hidden = true;
                }

                if (submitButton) {
                    if (!submitButton.dataset.originalText) {
                        submitButton.dataset.originalText = submitButton.textContent.trim();
                    }

                    submitButton.disabled = true;
                    submitButton.textContent = 'Gönderiliyor...';
                }

                try {
                    const response = await fetch(form.action || window.location.href, {
                        method: 'POST',
                        body: new FormData(form),
                        headers: {
                            'Accept': 'application/json',
                        },
                    });

                    const result = await response.json();

                    if (result.success) {
                        if (result.redirect) {
                            window.location.href = result.redirect;
                            return;
                        }

                        window.location.reload();
                        return;
                    }

                    if (result.errors && typeof result.errors === 'object') {
                        Object.entries(result.errors).forEach(([field, message]) => {
                            const errorElement = fieldErrors[field];

                            if (errorElement) {
                                errorElement.textContent = message;
                                errorElement.hidden = false;
                            }
                        });
                    }

                    if (result.message && generalError && generalErrorMessage) {
                        generalErrorMessage.textContent = result.message;
                        generalError.hidden = false;
                    }
                } catch (error) {
                    if (generalError && generalErrorMessage) {
                        generalErrorMessage.textContent = 'İşlem sırasında bir hata oluştu. Lütfen tekrar deneyin.';
                        generalError.hidden = false;
                    }
                } finally {
                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.textContent = submitButton.dataset.originalText || 'Giriş Yap';
                    }
                }
            });
        });
    </script>
    </main>
</body>
</html>
