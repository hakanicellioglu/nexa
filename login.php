<?php
session_start();
require_once __DIR__ . '/toast.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error_messages = [
    'invalid_credentials' => 'Kullanıcı adı veya şifre hatalı.',
    'missing_fields'      => 'Lütfen kullanıcı adı ve parolanızı giriniz.',
    'account_locked'      => 'Hesabınız geçici olarak kilitlendi. Lütfen daha sonra tekrar deneyin.',
    'session_expired'     => 'Oturumunuz sona erdi. Lütfen tekrar giriş yapın.',
    'too_many_attempts'   => 'Çok fazla başarısız deneme. Lütfen 15 dakika bekleyin.',
];

$error_key = null;
$toasts = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error_key = 'missing_fields';
    } else {
        $pdoFromConfig = require_once __DIR__ . '/config.php';
        if (!$pdoFromConfig instanceof PDO) {
            throw new RuntimeException('Veritabanı bağlantısı yapılandırılamadı.');
        }
        $pdo = $pdoFromConfig;

        $stmt = $pdo->prepare('SELECT id, password FROM users WHERE username = ? OR email = ? LIMIT 1');
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            header('Location: dashboard.php');
            exit;
        }

        $error_key = 'invalid_credentials';
    }
}

if ($error_key && isset($error_messages[$error_key])) {
    $toasts = toast($error_messages[$error_key], 'error');
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap — Şirket Paneli</title>

    <!-- Google Fonts: DM Sans + DM Serif Display -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans:    ['"DM Sans"', 'sans-serif'],
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
                    keyframes: {
                        'fade-in-up': {
                            '0%':   { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        'slide-down': {
                            '0%':   { opacity: '0', transform: 'translateY(-8px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        spin: {
                            to: { transform: 'rotate(360deg)' },
                        },
                        shimmer: {
                            '0%':   { backgroundPosition: '-200% 0' },
                            '100%': { backgroundPosition: '200% 0' },
                        },
                    },
                    animation: {
                        'fade-in-up': 'fade-in-up 0.55s cubic-bezier(.22,1,.36,1) both',
                        'slide-down': 'slide-down 0.3s ease both',
                        'spin-fast': 'spin 0.7s linear infinite',
                        shimmer:     'shimmer 1.8s linear infinite',
                    },
                },
            },
        };
    </script>

    <style>
        /* ── Arka plan grid ── */
        .bg-grid {
            background-image:
                linear-gradient(to right, rgba(148,163,184,.09) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(148,163,184,.09) 1px, transparent 1px);
            background-size: 32px 32px;
        }

        /* ── Gradient ışık lekesi ── */
        .light-blob {
            background: radial-gradient(ellipse 70% 55% at 50% 40%,
                rgba(26,92,255,.13) 0%,
                rgba(26,92,255,.04) 55%,
                transparent 100%);
        }

        /* ── Input glow ── */
        .input-field {
            transition: border-color .2s, box-shadow .2s;
        }
        .input-field:focus {
            outline: none;
            border-color: #1a5cff;
            box-shadow: 0 0 0 3.5px rgba(26,92,255,.18), 0 1px 3px rgba(0,0,0,.08);
        }

        /* ── Loading butonu shimmer ── */
        .btn-loading {
            background-size: 200% 100%;
            background-image: linear-gradient(90deg,
                #003df5 0%,
                #4a85ff 50%,
                #003df5 100%);
            animation: shimmer 1.8s linear infinite;
            cursor: not-allowed;
        }

        /* ── Card cam efekti ── */
        .card-glass {
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(16px) saturate(180%);
            -webkit-backdrop-filter: blur(16px) saturate(180%);
        }

        /* ── Sosyal medya ikonları ── */
        .social-icon {
            transition: color .2s, transform .2s;
        }
        .social-icon:hover {
            color: #1a5cff;
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="min-h-screen font-sans bg-slate-50 bg-grid flex flex-col items-center justify-center px-4 relative overflow-hidden">

    <!-- Arka plan ışık lekesi -->
    <div class="light-blob absolute inset-0 pointer-events-none"></div>

    <!-- ══════════════════════════════════════
         ANA KART
    ══════════════════════════════════════ -->
    <main class="w-full max-w-md relative z-10 animate-fade-in-up">

        <!-- Üst logo / başlık -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-brand-600 shadow-lg shadow-brand-500/30 mb-4">
                <!-- Kilit ikonu (SVG inline) -->
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h1 class="font-display text-3xl text-slate-800 leading-tight">Hoş Geldiniz</h1>
            <p class="mt-1 text-sm text-slate-500">Hesabınıza giriş yapın</p>
        </div>

        <!-- Kart -->
        <div class="card-glass rounded-2xl border border-slate-200/80 shadow-xl shadow-slate-200/60 p-8">
            <?= $toasts ?>
            <!-- ── Form ── -->
            <form id="loginForm" action="login.php" method="post" novalidate>

                <!-- Kullanıcı adı -->
                <div class="mb-5">
                    <label for="username" class="block mb-1.5 text-sm font-semibold text-slate-700">
                        Kullanıcı Adı veya E-posta
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zm-4 7a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </span>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            autocomplete="username"
                            required
                            placeholder="ornek@sirket.com"
                            class="input-field w-full h-11 pl-10 pr-4 rounded-xl border border-slate-300 bg-white text-sm text-slate-800 placeholder-slate-400"
                        >
                    </div>
                </div>

                <!-- Parola -->
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="text-sm font-semibold text-slate-700">Parola</label>
                        <a href="#" class="text-xs text-brand-600 hover:text-brand-700 hover:underline font-medium transition-colors">
                            Şifremi unuttum
                        </a>
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </span>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            autocomplete="current-password"
                            required
                            placeholder="••••••••"
                            class="input-field w-full h-11 pl-10 pr-12 rounded-xl border border-slate-300 bg-white text-sm text-slate-800 placeholder-slate-400"
                        >
                        <!-- Şifre gizle/göster -->
                        <button
                            type="button"
                            id="togglePassword"
                            aria-label="Parolayı göster / gizle"
                            class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-brand-600 transition-colors"
                        >
                            <!-- Göz ikonu (açık) -->
                            <svg id="eyeOpen" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm-3 9C6.477 21 1 16.523 1 12S6.477 3 12 3s11 4.477 11 9-4.477 9-11 9z"/>
                            </svg>
                            <!-- Göz ikonu (kapalı) -->
                            <svg id="eyeClosed" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M13.875 18.825A10.05 10.05 0 0112 19c-5.523 0-10-4.477-10-7 0-1.266.672-2.607 1.854-3.79M6.343 6.343A9.956 9.956 0 0112 5c5.523 0 10 4.477 10 7 0 1.09-.37 2.234-1.05 3.29M3 3l18 18"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Giriş Butonu -->
                <button
                    id="submitBtn"
                    type="submit"
                    class="relative w-full h-11 rounded-xl bg-brand-600 hover:bg-brand-700 active:scale-[.98] text-white text-sm font-semibold tracking-wide shadow-md shadow-brand-500/25 transition-all duration-200 flex items-center justify-center gap-2 overflow-hidden"
                >
                    <!-- Normal durum -->
                    <span id="btnLabel">Giriş Yap</span>
                    <!-- Loading spinner (gizli) -->
                    <svg id="btnSpinner" xmlns="http://www.w3.org/2000/svg" class="hidden w-4 h-4 animate-spin-fast" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                        <path class="opacity-80" fill="currentColor"
                              d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                    </svg>
                    <span id="btnLoadingLabel" class="hidden">Doğrulanıyor…</span>
                </button>
            </form>
        </div><!-- /card -->

        <!-- Yardım metni -->
        <p class="mt-5 text-center text-xs text-slate-400">
            Hesabınız yok mu?
            <a href="#" class="text-brand-600 hover:underline font-medium">Yöneticinizle iletişime geçin</a>
        </p>
    </main>

    <!-- ══════════════════════════════════════
         FOOTER
    ══════════════════════════════════════ -->
    <footer class="relative z-10 mt-10 flex flex-col items-center gap-3 pb-6">
        <p class="text-xs text-slate-400 font-medium tracking-wide">Şirket Paneli <span class="text-slate-300">·</span> v1.0</p>

        <!-- Sosyal medya ikonları -->
        <div class="flex items-center gap-4 text-slate-400">

            <!-- Twitter/X -->
            <a href="#" aria-label="Twitter" class="social-icon">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                </svg>
            </a>

            <!-- LinkedIn -->
            <a href="#" aria-label="LinkedIn" class="social-icon">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                </svg>
            </a>

            <!-- GitHub -->
            <a href="#" aria-label="GitHub" class="social-icon">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/>
                </svg>
            </a>
        </div>

        <p class="text-[10px] text-slate-300">© 2025 Şirket A.Ş. Tüm hakları saklıdır.</p>
    </footer>

    <!-- ══════════════════════════════════════
         VANILLA JS
    ══════════════════════════════════════ -->
    <script>
    /* ── 1. Şifre gizle / göster ── */
    (function () {
        const toggle   = document.getElementById('togglePassword');
        const input    = document.getElementById('password');
        const eyeOpen  = document.getElementById('eyeOpen');
        const eyeClosed = document.getElementById('eyeClosed');

        toggle.addEventListener('click', () => {
            const isHidden = input.type === 'password';
            input.type     = isHidden ? 'text' : 'password';
            eyeOpen.classList.toggle('hidden', isHidden);
            eyeClosed.classList.toggle('hidden', !isHidden);
        });
    })();

    /* ── 2. Giriş butonu loading animasyonu ── */
    (function () {
        const form        = document.getElementById('loginForm');
        const btn         = document.getElementById('submitBtn');
        const btnLabel    = document.getElementById('btnLabel');
        const btnSpinner  = document.getElementById('btnSpinner');
        const btnLoading  = document.getElementById('btnLoadingLabel');

        form.addEventListener('submit', function (e) {
            // Basit HTML5 doğrulama geçmemişse animasyon başlatma
            if (!form.checkValidity()) return;

            // Loading durumuna geç
            btnLabel.classList.add('hidden');
            btnSpinner.classList.remove('hidden');
            btnLoading.classList.remove('hidden');
            btn.classList.add('btn-loading');
            btn.disabled = true;

            // NOT: Gerçek submit yine devam eder (e.preventDefault() YOK).
            // Test için sunucu yok ise aşağıdaki satırı aktive edin:
            // e.preventDefault();
        });
    })();
    </script>
</body>
</html>
