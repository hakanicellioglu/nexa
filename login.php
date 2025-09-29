<?php
session_start();

if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

require_once __DIR__ . '/config.php';

$errors = [];
$success = '';
$identifier = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($identifier === '') {
        $errors[] = 'Lütfen e-posta adresinizi veya kullanıcı adınızı girin.';
    }

    if ($password === '') {
        $errors[] = 'Lütfen parolanızı girin.';
    }

    if (empty($errors)) {
        $stmt = $connection->prepare('SELECT id, firstname, username, password FROM users WHERE email = ? OR username = ? LIMIT 1');

        if ($stmt === false) {
            $errors[] = 'Giriş sırasında bir hata oluştu. Lütfen daha sonra tekrar deneyin.';
        } else {
            $stmt->bind_param('ss', $identifier, $identifier);

            if ($stmt->execute()) {
                $stmt->store_result();

                if ($stmt->num_rows === 1) {
                    $stmt->bind_result($userId, $dbFirstname, $dbUsername, $hashedPassword);
                    $stmt->fetch();

                    if (password_verify($password, $hashedPassword)) {
                        $_SESSION['user_id'] = $userId;
                        $_SESSION['username'] = $dbUsername;
                        header('Location: dashboard.php');
                        exit;
                    } else {
                        $errors[] = 'Parola hatalı. Lütfen tekrar deneyin.';
                    }
                } else {
                    $errors[] = 'Bu bilgilerle eşleşen bir kullanıcı bulunamadı.';
                }
            } else {
                $errors[] = 'Giriş sırasında bir hata oluştu. Lütfen tekrar deneyin.';
            }

            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Oturum Aç - Nexa</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #e1e1e1;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            color: #282828;
        }

        .card {
            background: #ffffff;
            padding: 2.5rem 3rem;
            border-radius: 12px;
            box-shadow: 0 18px 40px rgba(40, 40, 40, 0.18);
            width: min(100%, 420px);
        }

        h1 {
            margin-top: 0;
            margin-bottom: 1.5rem;
            color: #282828;
            text-align: center;
        }

        form {
            display: grid;
            gap: 1rem;
        }

        label {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
            font-weight: 600;
            color: #464646;
        }

        input {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            border: 1px solid #7d7d7d;
            font-size: 1rem;
            background: #ffffff;
            color: #282828;
        }

        button {
            padding: 0.9rem 1.2rem;
            border: none;
            border-radius: 999px;
            background: #282828;
            color: #ffffff;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s ease, color 0.2s ease;
        }

        button:hover {
            background: #464646;
            color: #ffffff;
        }

        .messages {
            margin-bottom: 1rem;
            display: grid;
            gap: 0.75rem;
        }

        .error {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            background: rgba(40, 40, 40, 0.08);
            color: #282828;
            border-left: 4px solid #282828;
        }

        .success {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            background: rgba(125, 125, 125, 0.12);
            color: #282828;
            border-left: 4px solid #7d7d7d;
        }

        .redirect {
            margin-top: 1.25rem;
            text-align: center;
            color: #464646;
        }

        .redirect a {
            color: #282828;
            text-decoration: none;
            font-weight: 600;
        }

        .redirect a:hover {
            text-decoration: underline;
            color: #464646;
        }
    </style>
</head>
<body>
    <main class="card">
        <h1>Oturum Aç</h1>

        <?php if (!empty($errors) || $success !== ''): ?>
            <div class="messages">
                <?php foreach ($errors as $error): ?>
                    <div class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endforeach; ?>

                <?php if ($success !== ''): ?>
                    <div class="success"><?php echo $success; ?></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="">
            <label>
                E-posta veya Kullanıcı Adı
                <input
                    type="text"
                    name="identifier"
                    value="<?php echo htmlspecialchars($identifier, ENT_QUOTES, 'UTF-8'); ?>"
                    placeholder="ornek@mail.com veya kullaniciadi"
                    required
                />
            </label>

            <label>
                Parola
                <input type="password" name="password" placeholder="Parolanız" required />
            </label>

            <button type="submit">Giriş Yap</button>
        </form>

        <p class="redirect">
            Hesabınız yok mu? <a href="register.php">Kayıt olun</a> veya <a href="index.php">ana sayfaya dönün</a>.
        </p>
    </main>
</body>
</html>
