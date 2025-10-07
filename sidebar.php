<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$user = current_user();

if ($user === null) {
    return;
}
?>
<aside class="sidebar col-12 col-lg-2 d-flex flex-column p-3 text-white">
    <h5 class="text-white mb-4">Kontrol Paneli</h5>
    <ul class="nav nav-pills flex-column gap-1">
        <li class="nav-item"><a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>" href="/dashboard.php"><i class="bi bi-speedometer me-2"></i>Özet</a></li>
        <li class="nav-item"><a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'company.php' ? 'active' : ''; ?>" href="/company.php"><i class="bi bi-building me-2"></i>Şirket</a></li>
    </ul>
</aside>
