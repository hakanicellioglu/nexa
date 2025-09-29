<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Nexa Portal</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            min-height: 100vh;
            align-items: center;
            justify-content: center;
            background: #f4f6f8;
        }

        .container {
            text-align: center;
            background: #ffffff;
            padding: 3rem 4rem;
            border-radius: 12px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.12);
        }

        h1 {
            margin-bottom: 1.5rem;
            color: #1f2933;
            font-size: 2.25rem;
        }

        p {
            margin-bottom: 2.5rem;
            color: #52606d;
            font-size: 1.1rem;
        }

        .actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            justify-content: center;
        }

        .actions a {
            text-decoration: none;
        }

        .actions button {
            padding: 0.85rem 2.4rem;
            font-size: 1rem;
            border-radius: 999px;
            border: none;
            cursor: pointer;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .actions button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 22px rgba(79, 70, 229, 0.25);
        }

        .login {
            background: #6366f1;
            color: #ffffff;
        }

        .register {
            background: #f97316;
            color: #ffffff;
        }
    </style>
</head>
<body>
    <main class="container">
        <h1>Nexa'ya Hoş Geldiniz</h1>
        <p>Devam etmek için oturum açın veya yeni bir hesap oluşturun.</p>
        <div class="actions">
            <a href="login.php">
                <button class="login" type="button">Oturum Aç</button>
            </a>
            <a href="register.php">
                <button class="register" type="button">Kayıt Ol</button>
            </a>
        </div>
    </main>
</body>
</html>