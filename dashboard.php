<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userFullName = trim(($_SESSION['firstname'] ?? '') . ' ' . ($_SESSION['lastname'] ?? ''));
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontrol Paneli</title>
    <?php include __DIR__ . '/fonts/monoton.php'; ?>
    <?php include __DIR__ . '/cdn/bootstrap.php'; ?>
    <style>
        body {
            background-color: #f1f3f5;
            font-family: 'Inter', sans-serif;
        }

        .dashboard-wrapper {
            display: grid;
            grid-template-columns: 280px 1fr;
            min-height: 100vh;
        }

        .content-area {
            padding: 2rem;
        }

        @media (max-width: 992px) {
            .dashboard-wrapper {
                grid-template-columns: 1fr;
            }

            .content-area {
                padding: 1.5rem 1rem 3rem;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include __DIR__ . '/sidebar.php'; ?>
        <main class="content-area">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <div>
                    <h1 class="h3 fw-bold">Merhaba, <?= htmlspecialchars($userFullName !== '' ? $userFullName : ($_SESSION['username'] ?? 'Kullanıcı'), ENT_QUOTES, 'UTF-8') ?>!</h1>
                    <p class="text-muted mb-0">Bugünkü özetinizi aşağıda bulabilirsiniz.</p>
                </div>
                <a href="logout.php" class="btn btn-outline-danger">Çıkış yap</a>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h2 class="h6 text-uppercase text-muted">Aktif Projeler</h2>
                            <p class="display-6 fw-bold mb-0">5</p>
                            <small class="text-success">+2 yeni proje</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h2 class="h6 text-uppercase text-muted">Aylık Siparişler</h2>
                            <p class="display-6 fw-bold mb-0">32</p>
                            <small class="text-primary">%8 artış</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h2 class="h6 text-uppercase text-muted">Tedarikçi Güncellemeleri</h2>
                            <p class="display-6 fw-bold mb-0">7</p>
                            <small class="text-warning">3 beklemede</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body">
                    <h2 class="h5 fw-bold">Hızlı Erişim</h2>
                    <p class="text-muted">Sık kullanılan araçlarınıza buradan ulaşabilirsiniz.</p>
                    <div class="row g-3">
                        <div class="col-sm-6 col-lg-3">
                            <a href="#" class="btn btn-light w-100 py-3 border rounded-3 shadow-sm">Yeni Sipariş</a>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <a href="#" class="btn btn-light w-100 py-3 border rounded-3 shadow-sm">Proje Oluştur</a>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <a href="#" class="btn btn-light w-100 py-3 border rounded-3 shadow-sm">Fiyat Listesi</a>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <a href="#" class="btn btn-light w-100 py-3 border rounded-3 shadow-sm">Tedarikçi Ekle</a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
