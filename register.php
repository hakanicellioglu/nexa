<?php
session_start();
require_once __DIR__ . '/toast.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$successMessage = null;
$errors = [];
$firstname = '';
$lastname = '';
$username = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($firstname === '') {
        $errors[] = 'Ad alanı zorunludur.';
    }

    if ($lastname === '') {
        $errors[] = 'Soyad alanı zorunludur.';
    }

    if ($username === '') {
        $errors[] = 'Kullanıcı adı zorunludur.';
    } elseif (!preg_match('/^[a-zA-Z0-9_\.\-]{3,80}$/', $username)) {
        $errors[] = 'Kullanıcı adı yalnızca harf, rakam, nokta, alt çizgi ve kısa çizgi içerebilir.';
    }

    if ($email === '') {
        $errors[] = 'E-posta adresi zorunludur.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Geçerli bir e-posta adresi giriniz.';
    }

    if ($password === '') {
        $errors[] = 'Parola zorunludur.';
    } elseif (mb_strlen($password) < 8) {
        $errors[] = 'Parola en az 8 karakter olmalıdır.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Parolalar eşleşmiyor.';
    }

    if (empty($errors)) {
        $pdoFromConfig = require_once __DIR__ . '/config.php';
        if (!$pdoFromConfig instanceof PDO) {
            throw new RuntimeException('Veritabanı bağlantısı yapılandırılamadı.');
        }
        $pdo = $pdoFromConfig;

        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ? OR username = ?');
        $stmt->execute([$email, $username]);
        $duplicateCount = (int) $stmt->fetchColumn();

        if ($duplicateCount > 0) {
            $errors[] = 'Bu e-posta ya da kullanıcı adı zaten kullanılıyor.';
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $insert = $pdo->prepare('INSERT INTO users (firstname, lastname, username, email, password) VALUES (?, ?, ?, ?, ?)');
            $insert->execute([$firstname, $lastname, $username, $email, $passwordHash]);

            $successMessage = 'Kayıt işlemi başarılı! Giriş yapmak için lütfen kullanıcı adı ve parolanızla giriş yapın.';
            $firstname = '';
            $lastname = '';
            $username = '';
            $email = '';
        }
    }
}

$toasts = '';
if (!empty($errors)) {
    foreach ($errors as $error) {
        $toasts .= toast($error, 'error');
    }
}
if ($successMessage !== null) {
    $toasts .= toast($successMessage, 'success');
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol — Şirket Paneli</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"DM Sans"', 'sans-serif'],
                        display: ['"DM Serif Display"', 'serif'],
                    },
                    colors: {
                        brand: {
                            50:  '#eef4ff',
                            100: '#dce8ff',
                            200: '#b9d1ff',
                            300: '#85b0ff',
                            400: '#4a85ff',
                            500: '#1a5cff',
                            600: '#003df5',
                            700: '#002dd4',
                            800: '#0027ac',
                            900: '#052387',
                        },
                    },
                },
            },
        };
    </script>
    <style>
        .bg-grid {
            background-image:
                linear-gradient(to right, rgba(148,163,184,.09) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(148,163,184,.09) 1px, transparent 1px);
            background-size: 32px 32px;
        }
        .light-blob {
            background: radial-gradient(ellipse 70% 55% at 50% 40%,
                rgba(26,92,255,.13) 0%,
                rgba(26,92,255,.04) 55%,
                transparent 100%);
        }
        .input-field {
            transition: border-color .2s, box-shadow .2s;
        }
        .input-field:focus {
            outline: none;
            border-color: #1a5cff;
            box-shadow: 0 0 0 3px rgba(26,92,255,.15), 0 1px 3px rgba(0,0,0,.08);
        }
        .card-glass {
            background: rgba(255,255,255,0.93);
            backdrop-filter: blur(14px) saturate(180%);
            -webkit-backdrop-filter: blur(14px) saturate(180%);
        }
    </style>
</head>
<body class="min-h-screen font-sans bg-slate-50 bg-grid flex items-center justify-center px-4 py-10">
    <div class="light-blob pointer-events-none absolute inset-0"></div>
    <main class="relative w-full max-w-lg">
        <div class="mb-8 text-center">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-3xl bg-brand-600 text-white shadow-lg shadow-brand-500/25 mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 11c2.209 0 4-1.791 4-4S14.209 3 12 3 8 4.791 8 7s1.791 4 4 4zm0 2c-2.667 0-8 1.333-8 4v3h16v-3c0-2.667-5.333-4-8-4z"/>
                </svg>
            </div>
            <h1 class="text-3xl font-semibold text-slate-900">Kayıt Ol</h1>
            <p class="mt-2 text-sm text-slate-600">Hesabınızı oluşturun ve panelinize erişin.</p>
        </div>

        <div class="card-glass rounded-3xl border border-slate-200/80 shadow-xl shadow-slate-200/60 p-8">
            <?= $toasts ?>
            <form action="register.php" method="post" novalidate>
                <div class="space-y-5">
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label for="firstname" class="mb-2 block text-sm font-semibold text-slate-700">Ad</label>
                            <input
                                type="text"
                                id="firstname"
                                name="firstname"
                                value="<?= htmlspecialchars($firstname) ?>"
                                autocomplete="given-name"
                                required
                                class="input-field w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder-slate-400"
                                placeholder="Örnek"
                            >
                        </div>
                        <div>
                            <label for="lastname" class="mb-2 block text-sm font-semibold text-slate-700">Soyad</label>
                            <input
                                type="text"
                                id="lastname"
                                name="lastname"
                                value="<?= htmlspecialchars($lastname) ?>"
                                autocomplete="family-name"
                                required
                                class="input-field w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder-slate-400"
                                placeholder="Kullanıcı"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="username" class="mb-2 block text-sm font-semibold text-slate-700">Kullanıcı Adı</label>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            value="<?= htmlspecialchars($username) ?>"
                            autocomplete="username"
                            required
                            class="input-field w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder-slate-400"
                            placeholder="kullaniciadi123"
                        >
                    </div>

                    <div>
                        <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">E-posta</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="<?= htmlspecialchars($email) ?>"
                            autocomplete="email"
                            required
                            class="input-field w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder-slate-400"
                            placeholder="ornek@posta.com"
                        >
                    </div>

                    <div>
                        <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Parola</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            class="input-field w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder-slate-400"
                            placeholder="********"
                        >
                    </div>

                    <div>
                        <label for="confirm_password" class="mb-2 block text-sm font-semibold text-slate-700">Parolayı Onayla</label>
                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            required
                            class="input-field w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder-slate-400"
                            placeholder="********"
                        >
                    </div>
                </div>

                <button type="submit" class="mt-8 inline-flex w-full items-center justify-center rounded-2xl bg-brand-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-brand-500/20 transition hover:bg-brand-700">Kaydı Tamamla</button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-600">
                Zaten hesabınız var mı?
                <a href="login.php" class="font-semibold text-brand-600 hover:text-brand-700">Giriş yapın.</a>
            </p>
        </div>
    </main>
</body>
</html>
