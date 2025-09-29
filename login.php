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
            background: #E6F4F1;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .card {
            background: #FFFFFF;
            padding: 2.5rem 3rem;
            border-radius: 12px;
            box-shadow: 0 18px 40px rgba(0, 56, 64, 0.12);
            width: min(100%, 420px);
        }

        h1 {
            margin-top: 0;
            margin-bottom: 1.5rem;
            color: #003840;
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
            color: #005A5B;
        }

        input {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            border: 1px solid #8FD6C8;
            font-size: 1rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        input:focus {
            outline: none;
            border-color: #008C72;
            box-shadow: 0 0 0 3px rgba(0, 140, 114, 0.25);
        }

        button {
            padding: 0.9rem 1.2rem;
            border: none;
            border-radius: 999px;
            background: #008C72;
            color: #FFFFFF;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }

        button:hover {
            background: #007369;
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(0, 115, 105, 0.25);
        }

        .messages {
            margin-bottom: 1rem;
            display: grid;
            gap: 0.75rem;
        }

        .error {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            background: rgba(0, 56, 64, 0.12);
            color: #003840;
        }

        .success {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            background: rgba(2, 166, 118, 0.12);
            color: #007369;
        }

        .redirect {
            margin-top: 1.25rem;
            text-align: center;
            color: #005A5B;
        }

        .redirect a {
            color: #02A676;
            text-decoration: none;
            font-weight: 600;
        }

        .redirect a:hover {
            text-decoration: underline;
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
