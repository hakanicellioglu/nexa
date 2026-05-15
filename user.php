<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pdoFromConfig = require_once __DIR__ . '/config.php';
if (!$pdoFromConfig instanceof PDO) {
    throw new RuntimeException('Veritabanı bağlantısı yapılandırılmadı.');
}
$pdo = $pdoFromConfig;

$stmt = $pdo->prepare('SELECT firstname, lastname, email FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $user['name'] = trim($user['firstname'] . ' ' . $user['lastname']);
    $user['role'] = 'Kullanıcı';
    $user['joined'] = '—';
}

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kullanıcı Profili</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 font-sans">
    <div class="max-w-4xl mx-auto px-4 py-10">
        <header class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <p class="text-sm text-slate-500">Profil</p>
                    <h1 class="text-3xl font-semibold text-slate-900">Hoş geldiniz, <?= htmlspecialchars($user['name']) ?></h1>
                    <p class="mt-2 text-sm text-slate-600">Hesabınıza ait temel bilgiler aşağıda yer alıyor.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="dashboard.php" class="inline-flex items-center justify-center rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Dashboard</a>
                    <a href="login.php" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Oturumu Sonlandır</a>
                </div>
            </div>
        </header>

        <section class="grid gap-6 lg:grid-cols-[1.8fr_1fr]">
            <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Hesap Detayları</h2>
                <dl class="space-y-5 text-sm text-slate-700">
                    <div class="grid gap-2 sm:grid-cols-[140px_1fr]">
                        <dt class="font-medium text-slate-600">Ad Soyad</dt>
                        <dd><?= htmlspecialchars($user['name']) ?></dd>
                    </div>
                    <div class="grid gap-2 sm:grid-cols-[140px_1fr]">
                        <dt class="font-medium text-slate-600">E-posta</dt>
                        <dd><?= htmlspecialchars($user['email']) ?></dd>
                    </div>
                    <div class="grid gap-2 sm:grid-cols-[140px_1fr]">
                        <dt class="font-medium text-slate-600">Rol</dt>
                        <dd><?= htmlspecialchars($user['role']) ?></dd>
                    </div>
                    <div class="grid gap-2 sm:grid-cols-[140px_1fr]">
                        <dt class="font-medium text-slate-600">Üyelik Tarihi</dt>
                        <dd><?= htmlspecialchars($user['joined']) ?></dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Profil Özetiniz</h2>
                <p class="text-sm leading-7 text-slate-600">Bu sayfa, oturum açmış kullanıcı için basit bir profil görüntüleme sayfasıdır. Gerekirse buraya parola değişikliği, favori ayarlar veya ek kullanıcı bilgileri ekleyebilirsiniz.</p>
                <div class="mt-6 rounded-2xl bg-slate-50 p-4 text-sm text-slate-700">
                    <p class="font-medium text-slate-900">Kullanıcı ID</p>
                    <p class="mt-1 text-slate-600"><?= htmlspecialchars((string)$_SESSION['user_id']) ?></p>
                </div>
            </div>
        </section>
    </div>
</body>
</html>
