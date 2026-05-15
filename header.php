<?php
// Dashboard header shared component.
// Expects $user and icon() helper to be available in the including file.
if (!isset($user) || !is_array($user)) {
    $user = ['avatar' => ''];
}
$header_avatar = htmlspecialchars($user['avatar'] ?? '', ENT_QUOTES, 'UTF-8');
?>
<header class="sticky top-0 z-20 flex items-center justify-between px-6 py-4 bg-surface-900/80 backdrop-blur-md border-b border-white/5">
    <div>
        <h1 class="font-display text-xl text-white leading-none">Genel Bakış</h1>
        <p class="text-xs text-slate-500 mt-0.5"><?= date('d F Y, l') ?></p>
    </div>
    <div class="flex items-center gap-3">
        <button class="relative w-9 h-9 rounded-lg bg-surface-700 hover:bg-surface-600 flex items-center justify-center text-slate-400 hover:text-white transition-all" type="button">
            <?= icon('bell') ?>
            <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-brand-500 rounded-full animate-pulse-slow"></span>
        </button>
        <div class="w-9 h-9 rounded-lg bg-brand-500 flex items-center justify-center text-white text-xs font-bold shadow-md shadow-brand-500/30 cursor-pointer">
            <?= $header_avatar ?>
        </div>
    </div>
</header>
