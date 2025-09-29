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
            background: #E6F4F1;
        }

        .container {
            text-align: center;
            background: #FFFFFF;
            padding: 3rem 4rem;
            border-radius: 12px;
            box-shadow: 0 20px 45px rgba(0, 56, 64, 0.12);
        }

        h1 {
            margin-bottom: 1.5rem;
            color: #003840;
            font-size: 2.25rem;
        }

        p {
            margin-bottom: 2.5rem;
            color: #005A5B;
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
            box-shadow: 0 12px 22px rgba(0, 115, 105, 0.25);
        }

        .login {
            background: #008C72;
            color: #FFFFFF;
        }

        .register {
            background: #02A676;
            color: #FFFFFF;
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