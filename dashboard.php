<?php
session_start();

// ── Oturum kontrolü ──────────────────────────────────────────────────────────
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// ── Veritabanı bağlantısı (config.php'den) ───────────────────────────────────
$pdo = null;
$pdoFromConfig = require_once __DIR__ . '/config.php'; // $pdo (PDO nesnesi) burada tanımlanır
if ($pdoFromConfig instanceof PDO) {
    $pdo = $pdoFromConfig;
}
if (!$pdo instanceof PDO) {
    throw new RuntimeException('Veritabanı bağlantısı yapılandırılmadı.');
}

// ── Giriş yapan kullanıcı (SESSION'dan) ──────────────────────────────────────
$stmt = $pdo->prepare('SELECT firstname, lastname, username, email FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$user['name'] = trim($user['firstname'] . ' ' . $user['lastname']);
$user['role'] = 'Kullanıcı';
$user['avatar'] = mb_strtoupper(
    mb_substr($user['firstname'], 0, 1) .
    (mb_strlen($user['lastname']) ? mb_substr($user['lastname'], 0, 1) : '')
);

// ── İstatistik kartları ───────────────────────────────────────────────────────
$total_users   = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$active_sess   = $total_users;
$server_load   = 32;
$open_tickets  = 0;

// Geçen aya göre delta hesabı
$prev_users    = $total_users;
$user_delta    = 0;
$ticket_delta  = 0;

$stats = [
    [
        'label' => 'Toplam Kullanıcı',
        'value' => number_format($total_users, 0, ',', '.'),
        'delta' => '—',
        'up'    => true,
        'icon'  => 'users',
    ],
    [
        'label' => 'Aktif Kullanıcı',
        'value' => $active_sess,
        'delta' => '—',
        'up'    => true,
        'icon'  => 'activity',
    ],
    [
        'label' => 'Sunucu Yükü',
        'value' => '%' . $server_load,
        'delta' => $server_load < 70 ? 'Normal' : 'Yüksek',
        'up'    => $server_load < 70,
        'icon'  => 'server',
    ],
    [
        'label' => 'Açık Destek Talebi',
        'value' => $open_tickets,
        'delta' => '—',
        'up'    => true,
        'icon'  => 'ticket',
    ],
];

$activities = [];
$months = ['Oca', 'Şub', 'Mar', 'Nis', 'May', 'Haz', 'Tem'];
$vals   = array_fill(0, count($months), 0);

// ── Son eklenen kullanıcılar ──────────────────────────────────────────────────
$stmt = $pdo->query(
    "SELECT CONCAT(firstname, ' ', lastname) AS name, email, username, 'active' AS status,
            DATE_FORMAT(NOW(), '%e %b %Y') AS joined
     FROM users
     ORDER BY id DESC
     LIMIT 10"
);
$recent_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── Son aktiviteler ───────────────────────────────────────────────────────────
$activities = [];

// ── Grafik: son 7 ayın kullanıcı aktivitesi ───────────────────────────────────
// Mevcut SQL dökümünde yalnızca users tablosu bulunduğu için tablo verisi burada statik olarak atanır.
$chart_rows = [];
$months = ['Oca', 'Şub', 'Mar', 'Nis', 'May', 'Haz', 'Tem'];
$vals   = array_fill(0, count($months), 0);

// ── Navigasyon (aktif sayfa REQUEST_URI ile belirlenir) ───────────────────────
$current = basename($_SERVER['PHP_SELF']);
$nav_items = [
    ['icon' => 'home',      'label' => 'Genel Bakış',  'href' => 'dashboard.php'],
    ['icon' => 'users',     'label' => 'Kullanıcılar', 'href' => 'users.php'],
    ['icon' => 'bar-chart', 'label' => 'Raporlar',     'href' => 'reports.php'],
    ['icon' => 'settings',  'label' => 'Ayarlar',      'href' => 'settings.php'],
    ['icon' => 'bell',      'label' => 'Bildirimler',  'href' => 'notifications.php'],
];
foreach ($nav_items as &$item) {
    $item['active'] = (basename($item['href']) === $current);
}
unset($item);

// ── Okunmamış bildirim sayısı ─────────────────────────────────────────────────
$notif_count = 0;

/* ── İkon helper ── */
function icon(string $name): string {
    $paths = [
        'home'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.5 1.5 0 012.092 0L22.25 12M4.5 9.75v10.125A1.125 1.125 0 005.625 21h3.75a1.125 1.125 0 001.125-1.125V15a1.125 1.125 0 011.125-1.125h2.25A1.125 1.125 0 0115 15v4.875A1.125 1.125 0 0016.125 21h3.75A1.125 1.125 0 0021 19.875V9.75M8.25 21h7.5"/>',
        'users'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>',
        'bar-chart' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zm6.75-4.5c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25A1.125 1.125 0 019.75 19.875V8.625zm6.75-4.5c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>',
        'settings'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 010 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 010-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',
        'bell'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>',
        'activity'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/>',
        'server'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M21.75 17.25v.75a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25v-.75m19.5 0A2.25 2.25 0 0021.75 15v-.75M21.75 17.25H2.25m19.5-9.75v.75A2.25 2.25 0 0119.5 10.5h-15a2.25 2.25 0 01-2.25-2.25v-.75m19.5 0A2.25 2.25 0 0021.75 5.25v-.75a2.25 2.25 0 00-2.25-2.25h-15A2.25 2.25 0 002.25 4.5v.75m19.5 0H2.25"/>',
        'ticket'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a3 3 0 000-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z"/>',
        'logout'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/>',
        'menu'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>',
        'x'         => '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>',
        'publish'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z"/>',
        'edit'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>',
        'system'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 01-3-3m3 3a3 3 0 100 6h13.5a3 3 0 100-6m-16.5-3a3 3 0 013-3h13.5a3 3 0 013 3m-19.5 0a4.5 4.5 0 01.9-2.7L5.737 5.1a3.375 3.375 0 012.7-1.35h7.126c1.062 0 2.062.5 2.7 1.35l2.587 3.45a4.5 4.5 0 01.9 2.7m0 0a3 3 0 01-3 3m0 3h.008v.008h-.008v-.008zm0-6h.008v.008h-.008v-.008zm-3 6h.008v.008h-.008v-.008zm0-6h.008v.008h-.008v-.008z"/>',
        'user'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>',
    ];
    $d = $paths[$name] ?? '';
    return '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">'.$d.'</svg>';
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Şirket Paneli</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&family=DM+Serif+Display&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
        darkMode: 'class',
        theme: {
            extend: {
                fontFamily: {
                    sans:    ['"DM Sans"', 'sans-serif'],
                    display: ['"DM Serif Display"', 'serif'],
                },
                colors: {
                    surface: {
                        900: '#0c0f1a',
                        800: '#111629',
                        700: '#181e33',
                        600: '#1f2744',
                    },
                    brand: {
                        400: '#4a85ff',
                        500: '#1a5cff',
                        600: '#003df5',
                    },
                },
                keyframes: {
                    'fade-in': {
                        '0%':   { opacity: '0', transform: 'translateY(12px)' },
                        '100%': { opacity: '1', transform: 'translateY(0)' },
                    },
                    'bar-grow': {
                        '0%':   { transform: 'scaleY(0)' },
                        '100%': { transform: 'scaleY(1)' },
                    },
                    pulse: {
                        '0%,100%': { opacity: '1' },
                        '50%':     { opacity: '.4' },
                    },
                },
                animation: {
                    'fade-in':  'fade-in 0.5s cubic-bezier(.22,1,.36,1) both',
                    'bar-grow': 'bar-grow 0.8s cubic-bezier(.22,1,.36,1) both',
                    'pulse-slow': 'pulse 2.5s ease-in-out infinite',
                },
            },
        },
    };
    </script>
    <style>
        /* Scrollbar */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #1f2744; border-radius: 9999px; }

        /* Staggered card delays */
        .stat-card:nth-child(1) { animation-delay: .05s; }
        .stat-card:nth-child(2) { animation-delay: .12s; }
        .stat-card:nth-child(3) { animation-delay: .19s; }
        .stat-card:nth-child(4) { animation-delay: .26s; }

        /* Chart bar origins */
        .bar { transform-origin: bottom; }

        /* Active nav item glow */
        .nav-active {
            background: linear-gradient(90deg, rgba(26,92,255,.18) 0%, rgba(26,92,255,.06) 100%);
            border-left: 2px solid #1a5cff;
        }

        /* Mavi gradient accent line on stat cards */
        .stat-accent {
            background: linear-gradient(90deg, #1a5cff, #4a85ff);
        }
    </style>
</head>
<body class="bg-surface-900 text-slate-300 font-sans min-h-screen flex overflow-hidden">

<!-- ═══════════════════════════════════════
     SIDEBAR
═══════════════════════════════════════ -->
<aside id="sidebar" class="relative w-64 shrink-0 bg-surface-800 border-r border-white/5 flex flex-col h-screen fixed z-40
                           -translate-x-full lg:translate-x-0">

    <!-- Logo -->
    <div class="flex items-center gap-3 px-6 py-5 border-b border-white/5">
        <div class="w-8 h-8 rounded-lg bg-brand-500 flex items-center justify-center shadow-lg shadow-brand-500/30">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/>
            </svg>
        </div>
        <div>
            <p class="text-sm font-semibold text-white leading-tight">Şirket Paneli</p>
            <p class="text-[10px] text-slate-500 leading-tight">v1.0</p>
        </div>
    </div>

    <!-- Nav -->
    <nav class="flex-1 overflow-y-auto py-4 px-3">
        <p class="px-3 mb-2 text-[10px] font-semibold tracking-widest text-slate-600 uppercase">Menü</p>
        <?php foreach ($nav_items as $item): ?>
        <a href="<?= htmlspecialchars($item['href']) ?>"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg mb-0.5 text-sm transition-all group
                  <?= $item['active']
                      ? 'nav-active text-brand-400 font-semibold'
                      : 'text-slate-400 hover:bg-white/5 hover:text-white' ?>">
            <?= icon($item['icon']) ?>
            <span><?= $item['label'] ?></span>
            <?php if ($item['label'] === 'Bildirimler' && $notif_count > 0): ?>
            <span class="ml-auto bg-brand-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">
                <?= $notif_count > 99 ? '99+' : $notif_count ?>
            </span>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </nav>

    <!-- Kullanıcı kutusu -->
    <div class="px-3 pb-5 border-t border-white/5 pt-4">
        <div class="flex items-center gap-3 px-3 py-2.5 rounded-xl bg-surface-700">
            <div class="w-8 h-8 rounded-full bg-brand-500 flex items-center justify-center text-white text-xs font-bold shrink-0">
                <?= $user['avatar'] ?>
            </div>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-white truncate"><?= htmlspecialchars($user['name']) ?></p>
                <p class="text-[11px] text-slate-500 truncate"><?= htmlspecialchars($user['role']) ?></p>
            </div>
            <a href="login.php" class="ml-auto text-slate-600 hover:text-red-400 transition-colors shrink-0" title="Çıkış">
                <?= icon('logout') ?>
            </a>
        </div>
    </div>
</aside>

<!-- ═══════════════════════════════════════
     ANA İÇERİK
═══════════════════════════════════════ -->
<div class="flex-1 flex flex-col min-h-screen overflow-auto">
    <?php require_once __DIR__ . '/header.php'; ?>

    <!-- ── Sayfa içeriği ── -->
    <main class="flex-1 px-6 py-8 space-y-8 max-w-7xl w-full mx-auto">

        <!-- ── 1. İSTATİSTİK KARTLARI ── -->
        <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <?php
            $icon_colors = ['bg-blue-500/15 text-blue-400','bg-emerald-500/15 text-emerald-400','bg-violet-500/15 text-violet-400','bg-amber-500/15 text-amber-400'];
            foreach ($stats as $i => $s):
            ?>
            <div class="stat-card animate-fade-in relative bg-surface-800 border border-white/5 rounded-2xl p-5 overflow-hidden group hover:border-brand-500/30 transition-all">
                <!-- Accent çizgi -->
                <div class="stat-accent absolute top-0 left-0 right-0 h-[2px] opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="flex items-start justify-between mb-4">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center <?= $icon_colors[$i] ?>">
                        <?= icon($s['icon']) ?>
                    </div>
                    <span class="text-xs font-semibold px-2 py-1 rounded-full <?= $s['up']
                        ? 'bg-emerald-500/10 text-emerald-400'
                        : 'bg-red-500/10 text-red-400' ?>">
                        <?= $s['up'] ? '↑' : '↓' ?> <?= $s['delta'] ?>
                    </span>
                </div>
                <p class="text-2xl font-bold text-white tracking-tight"><?= $s['value'] ?></p>
                <p class="text-xs text-slate-500 mt-1"><?= $s['label'] ?></p>
            </div>
            <?php endforeach; ?>
        </section>

        <!-- ── 2. GRAFİK + AKTİVİTE ── -->
        <section class="grid grid-cols-1 xl:grid-cols-3 gap-6">

            <!-- Çubuk grafik -->
            <div class="xl:col-span-2 bg-surface-800 border border-white/5 rounded-2xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-base font-semibold text-white">Aylık Kullanıcı Aktivitesi</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Son 7 ay</p>
                    </div>
                    <select class="bg-surface-700 border border-white/5 text-slate-400 text-xs rounded-lg px-3 py-1.5 focus:outline-none focus:border-brand-500">
                        <option><?= date('Y') ?></option>
                        <option><?= date('Y') - 1 ?></option>
                    </select>
                </div>
                <!-- SVG Grafik -->
                <div class="flex items-end justify-between gap-2 h-40 px-1">
                    <?php
                    $max = !empty($vals) ? max($vals) : 1;
                    foreach ($months as $j => $m):
                        $pct    = $max > 0 ? round($vals[$j] / $max * 100) : 0;
                        $delay  = 0.05 + $j * 0.07;
                        $isLast = $j === count($months) - 1;
                    ?>
                    <div class="flex-1 flex flex-col items-center gap-1.5 group">
                        <span class="text-[10px] text-slate-500 opacity-0 group-hover:opacity-100 transition-opacity -mb-1"><?= $vals[$j] ?></span>
                        <div class="w-full relative" style="height: <?= $pct ?>%">
                            <div class="bar absolute inset-0 rounded-t-md <?= $isLast ? 'bg-brand-500 shadow-lg shadow-brand-500/30' : 'bg-surface-600 group-hover:bg-surface-500' ?> transition-colors animate-bar-grow"
                                 style="animation-delay: <?= $delay ?>s"></div>
                        </div>
                        <span class="text-[10px] <?= $isLast ? 'text-brand-400 font-semibold' : 'text-slate-600' ?>"><?= $m ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Aktivite akışı -->
            <div class="bg-surface-800 border border-white/5 rounded-2xl p-6 flex flex-col">
                <h2 class="text-base font-semibold text-white mb-5">Son Aktiviteler</h2>
                <ul class="space-y-4 flex-1">
                    <?php
                    $act_icons = ['publish'=>'publish','edit'=>'edit','system'=>'server','user'=>'users','ticket'=>'ticket'];
                    $act_colors = ['publish'=>'text-blue-400 bg-blue-500/10','edit'=>'text-violet-400 bg-violet-500/10','system'=>'text-slate-400 bg-slate-500/10','user'=>'text-emerald-400 bg-emerald-500/10','ticket'=>'text-amber-400 bg-amber-500/10'];
                    foreach ($activities as $act):
                        $ic  = $act_icons[$act['type']];
                        $col = $act_colors[$act['type']];
                    ?>
                    <li class="flex items-start gap-3">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 <?= $col ?>">
                            <span class="scale-75 block"><?= icon($ic) ?></span>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-slate-300 leading-snug">
                                <span class="font-semibold text-white"><?= htmlspecialchars($act['user']) ?></span>
                                <?= htmlspecialchars($act['action']) ?>
                            </p>
                            <p class="text-[10px] text-slate-600 mt-0.5"><?= $act['time'] ?></p>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <a href="#" class="mt-5 text-center text-xs text-brand-400 hover:text-brand-300 font-medium transition-colors">
                    Tümünü gör →
                </a>
            </div>
        </section>

        <!-- ── 3. KULLANICI TABLOSU ── -->
        <section class="bg-surface-800 border border-white/5 rounded-2xl overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-white/5">
                <h2 class="text-base font-semibold text-white">Son Eklenen Kullanıcılar</h2>
                <a href="#" class="text-xs font-semibold text-brand-400 hover:text-brand-300 transition-colors">
                    Tüm kullanıcılar →
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-[11px] text-slate-600 uppercase tracking-wider border-b border-white/5">
                            <th class="text-left px-6 py-3 font-semibold">Ad Soyad</th>
                            <th class="text-left px-6 py-3 font-semibold hidden md:table-cell">Rol</th>
                            <th class="text-left px-6 py-3 font-semibold hidden lg:table-cell">Katılım</th>
                            <th class="text-left px-6 py-3 font-semibold">Durum</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $status_map = [
                            'active'   => ['Aktif',   'bg-emerald-500/10 text-emerald-400'],
                            'inactive' => ['Pasif',   'bg-slate-500/10 text-slate-500'],
                            'pending'  => ['Beklemede','bg-amber-500/10 text-amber-400'],
                        ];
                        foreach ($recent_users as $k => $u):
                            [$sl, $sc] = $status_map[$u['status']];
                        ?>
                        <tr class="border-b border-white/5 hover:bg-white/[.025] transition-colors group">
                            <td class="px-6 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-surface-600 flex items-center justify-center text-xs font-bold text-slate-300 shrink-0">
                                        <?= mb_substr($u['name'], 0, 1) ?>
                                    </div>
                                    <div>
                                        <p class="font-medium text-white text-sm"><?= htmlspecialchars($u['name']) ?></p>
                                        <p class="text-[11px] text-slate-500"><?= htmlspecialchars($u['email']) ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-3.5 text-slate-400 hidden md:table-cell"><?= htmlspecialchars($u['role']) ?></td>
                            <td class="px-6 py-3.5 text-slate-500 text-xs hidden lg:table-cell"><?= htmlspecialchars($u['joined']) ?></td>
                            <td class="px-6 py-3.5">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold <?= $sc ?>">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                    <?= $sl ?>
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-right">
                                <button class="opacity-0 group-hover:opacity-100 text-xs text-brand-400 hover:text-brand-300 font-medium transition-all">
                                    Düzenle
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </main>

    <!-- Footer -->
    <footer class="px-6 py-4 border-t border-white/5 flex items-center justify-between text-[11px] text-slate-600">
        <span>© <?= date('Y') ?> Şirket A.Ş.</span>
        <span>Şirket Paneli v1.0</span>
    </footer>
</div><!-- /main wrapper -->

</body>
</html>
