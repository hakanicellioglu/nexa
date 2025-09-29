<?php
require_once __DIR__ . '/config.php';

$errors = [];
$success = '';
$firstname = '';
$surname = '';
$email = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = trim($_POST['firstname'] ?? '');
    $surname = trim($_POST['surname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    if ($firstname === '') {
        $errors[] = 'Lütfen adınızı girin.';
    }

    if ($surname === '') {
        $errors[] = 'Lütfen soyadınızı girin.';
    }

    if ($email === '') {
        $errors[] = 'Lütfen e-posta adresinizi girin.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Lütfen geçerli bir e-posta adresi girin.';
    }

    if ($username === '') {
        $errors[] = 'Lütfen kullanıcı adınızı girin.';
    }

    if ($password === '') {
        $errors[] = 'Lütfen bir parola oluşturun.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Parola en az 6 karakter olmalıdır.';
    }

    if ($password !== $passwordConfirm) {
        $errors[] = 'Parolalar eşleşmiyor.';
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $connection->prepare('INSERT INTO users (firstname, surname, email, username, password) VALUES (?, ?, ?, ?, ?)');

        if ($stmt === false) {
            $errors[] = 'Kayıt sırasında bir hata oluştu. Lütfen daha sonra tekrar deneyin.';
        } else {
            $stmt->bind_param('sssss', $firstname, $surname, $email, $username, $hashedPassword);

            try {
                if ($stmt->execute()) {
                    $success = 'Kayıt işlemi başarılı! Oturum açmak için <a href="login.php">buraya tıklayın</a>.';
                    $firstname = $surname = $email = $username = '';
                } else {
                    if ($connection->errno === 1062) {
                        $errors[] = 'E-posta adresi veya kullanıcı adı zaten kayıtlı.';
                    } else {
                        $errors[] = 'Kayıt sırasında bir hata oluştu. Lütfen tekrar deneyin.';
                    }
                }
            } finally {
                $stmt->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kayıt Ol - Nexa</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f6f8;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .card {
            background: #ffffff;
            padding: 2.5rem 3rem;
            border-radius: 12px;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.12);
            width: min(100%, 480px);
        }

        h1 {
            margin-top: 0;
            margin-bottom: 1.5rem;
            color: #1f2933;
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
            color: #334155;
        }

        input {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            border: 1px solid #cbd5f5;
            font-size: 1rem;
        }

        button {
            padding: 0.9rem 1.2rem;
            border: none;
            border-radius: 999px;
            background: #6366f1;
            color: #ffffff;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        button:hover {
            background: #4f46e5;
        }

        .messages {
            margin-bottom: 1rem;
            display: grid;
            gap: 0.75rem;
        }

        .error {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            background: #fee2e2;
            color: #991b1b;
        }

        .success {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            background: #dcfce7;
            color: #166534;
        }

        .back-link {
            margin-top: 1.5rem;
            text-align: center;
        }

        .back-link a {
            color: #6366f1;
            text-decoration: none;
            font-weight: 600;
        }

        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <section class="card">
        <h1>Hesap Oluştur</h1>

        <?php if (!empty($errors)): ?>
            <div class="messages">
                <?php foreach ($errors as $error): ?>
                    <div class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($success !== ''): ?>
            <div class="messages">
                <div class="success"><?php echo $success; ?></div>
            </div>
        <?php endif; ?>

        <form method="post" action="">
            <label>
                Ad
                <input type="text" name="firstname" value="<?php echo htmlspecialchars($firstname ?? '', ENT_QUOTES, 'UTF-8'); ?>" required />
            </label>
            <label>
                Soyad
                <input type="text" name="surname" value="<?php echo htmlspecialchars($surname ?? '', ENT_QUOTES, 'UTF-8'); ?>" required />
            </label>
            <label>
                E-posta
                <input type="email" name="email" value="<?php echo htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8'); ?>" required />
            </label>
            <label>
                Kullanıcı Adı
                <input type="text" name="username" value="<?php echo htmlspecialchars($username ?? '', ENT_QUOTES, 'UTF-8'); ?>" required />
            </label>
            <label>
                Parola
                <input type="password" name="password" required />
            </label>
            <label>
                Parola (Tekrar)
                <input type="password" name="password_confirm" required />
            </label>
            <button type="submit">Kayıt Ol</button>
        </form>

        <div class="back-link">
            <a href="index.php">Ana sayfaya dön</a>
        </div>
    </section>
</body>
</html>
