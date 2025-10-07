<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$user = require_login();
$pdo = get_db_connection();

$companyStmt = $pdo->prepare('SELECT id, name, adres, phone, fax, website, created_at, updated_at FROM company WHERE user_id = :user_id LIMIT 1');
$companyStmt->execute([':user_id' => $user['id']]);
$company = $companyStmt->fetch() ?: null;

$pageTitle = 'Şirket Bilgileri - Nexa';
$csrfToken = ensure_csrf_token();
$hasSidebar = true;
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= e($csrfToken) ?>">
    <title><?= e($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --warning-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --info-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #111827;
        }

        .app-layout {
            min-height: 100vh;
        }

        .sidebar {
            background: rgba(17, 24, 39, 0.95);
            backdrop-filter: blur(10px);
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.75);
            font-weight: 500;
            transition: all 0.3s ease;
            border-radius: 10px;
            margin: 4px 0;
        }

        .sidebar .nav-link.active,
        .sidebar .nav-link:hover {
            color: #fff;
            background: var(--primary-gradient);
            transform: translateX(5px);
        }

        .sidebar .nav-link i {
            width: 1.5rem;
        }

        .main-content {
            min-height: 100vh;
            background: #f8f9fc;
            box-shadow: -10px 0 40px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 1;
        }

        .welcome-card {
            background: var(--primary-gradient);
            border-radius: 24px;
            color: white;
            padding: 2.5rem;
            position: relative;
            overflow: hidden;
            margin-bottom: 2.5rem;
            box-shadow: 0 25px 60px rgba(102, 126, 234, 0.35);
        }

        .welcome-card::before,
        .welcome-card::after {
            content: '';
            position: absolute;
            background: rgba(255, 255, 255, 0.12);
            border-radius: 50%;
            filter: blur(0.5px);
        }

        .welcome-card::before {
            top: -45%;
            right: -10%;
            width: 420px;
            height: 420px;
        }

        .welcome-card::after {
            bottom: -35%;
            left: -10%;
            width: 320px;
            height: 320px;
        }

        .welcome-card-content {
            position: relative;
            z-index: 1;
        }

        .welcome-card h1 {
            font-size: clamp(1.75rem, 3vw, 2.5rem);
            font-weight: 700;
        }

        .btn-gradient {
            background: var(--primary-gradient);
            border: none;
            color: #fff;
            padding: 0.75rem 1.6rem;
            border-radius: 14px;
            font-weight: 600;
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.35);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .btn-gradient:hover {
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 20px 50px rgba(118, 75, 162, 0.4);
        }

        .btn-ghost {
            background: rgba(255, 255, 255, 0.85);
            border: none;
            color: #1f2937;
            padding: 0.75rem 1.4rem;
            border-radius: 14px;
            font-weight: 600;
            transition: all 0.25s ease;
        }

        .btn-ghost:hover {
            background: rgba(255, 255, 255, 1);
            color: #111827;
            transform: translateY(-1px);
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.12);
        }

        .btn-soft-danger {
            background: rgba(239, 68, 68, 0.12);
            color: #b91c1c;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            padding: 0.5rem 1rem;
            transition: all 0.2s ease;
        }

        .btn-soft-danger:hover {
            background: rgba(239, 68, 68, 0.2);
            color: #991b1b;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.6);
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.12);
            backdrop-filter: blur(20px);
        }

        .section-card .card-body {
            padding: 2rem;
        }

        .detail-list dt {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6b7280;
        }

        .detail-list dd {
            font-size: 1rem;
            font-weight: 600;
            color: #111827;
        }

        .detail-list dd span.text-muted {
            font-weight: 500;
        }

        .meta-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }

        .meta-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.2);
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
            font-size: 0.9rem;
        }

        .form-control,
        .form-select,
        textarea {
            border-radius: 14px;
            border-color: rgba(148, 163, 184, 0.4);
            padding: 0.7rem 1rem;
        }

        .form-control:focus,
        .form-select:focus,
        textarea:focus {
            border-color: rgba(102, 126, 234, 0.6);
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.15);
        }

        .d-grid .btn,
        .d-md-flex .btn {
            border-radius: 14px;
        }

        @media (max-width: 767.98px) {
            .welcome-card {
                padding: 2rem;
            }

            .section-card .card-body {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
<div class="container-fluid app-layout">
    <div class="row flex-nowrap">
        <?php include __DIR__ . '/sidebar.php'; ?>
        <main class="col main-content px-3 px-lg-4 py-4">
            <?php if ($hasSidebar): ?>
                <button class="btn btn-outline-secondary d-lg-none mb-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
                    <i class="bi bi-list me-1"></i> Menü
                </button>
            <?php endif; ?>
            <div class="welcome-card">
                <div class="welcome-card-content d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-4">
                    <div>
                        <span class="text-uppercase fw-semibold small mb-2 d-inline-flex align-items-center gap-2">
                            <i class="bi bi-building"></i>
                            Şirket Bilgileri
                        </span>
                        <h1 class="mb-3">Şirket kartınızı modern ve düzenli tutun</h1>
                        <p class="mb-0 opacity-75">Tüm ekip arkadaşlarınız için güncel bilgileri paylaşın ve tek tıkla güncelleyin. Bu sayfa üzerinden şirket profilinizi yönetebilirsiniz.</p>
                        <?php if ($company): ?>
                            <div class="meta-badges">
                                <span class="meta-badge"><i class="bi bi-clock-history"></i>Son güncelleme: <?= e((new DateTimeImmutable($company['updated_at']))->format('d.m.Y H:i')) ?></span>
                                <span class="meta-badge"><i class="bi bi-calendar-event"></i>Oluşturma: <?= e((new DateTimeImmutable($company['created_at']))->format('d.m.Y H:i')) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex flex-column flex-sm-row gap-2">
                        <button id="toggleCompanyForm" class="btn btn-gradient">
                            <i class="bi bi-pencil-square me-2"></i><?= $company ? 'Bilgileri düzenle' : 'Yeni şirket ekle' ?>
                        </button>
                        <?php if (!$company): ?>
                            <button class="btn btn-ghost" type="button" onclick="window.location.href='dashboard.php'">
                                <i class="bi bi-speedometer2 me-2"></i>Panele dön
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-12 col-xl-6">
                    <div class="card glass-card border-0 h-100 section-card">
                        <div class="card-body">
                            <h2 class="h5 fw-semibold mb-4">Mevcut Durum</h2>
                            <?php if ($company): ?>
                                <dl class="row mb-0 detail-list g-4">
                                    <dt class="col-sm-4">Şirket adı</dt>
                                    <dd class="col-sm-8"><?= e($company['name']) ?></dd>

                                    <dt class="col-sm-4">Adres</dt>
                                    <dd class="col-sm-8"><?= $company['adres'] ? nl2br(e($company['adres'])) : '<span class="text-muted">Belirtilmemiş</span>' ?></dd>

                                    <dt class="col-sm-4">Telefon</dt>
                                    <dd class="col-sm-8"><?= $company['phone'] ? e($company['phone']) : '<span class="text-muted">Belirtilmemiş</span>' ?></dd>

                                    <dt class="col-sm-4">Faks</dt>
                                    <dd class="col-sm-8"><?= $company['fax'] ? e($company['fax']) : '<span class="text-muted">Belirtilmemiş</span>' ?></dd>

                                    <dt class="col-sm-4">Web sitesi</dt>
                                    <dd class="col-sm-8">
                                        <?php if ($company['website']): ?>
                                            <a href="<?= e($company['website']) ?>" target="_blank" rel="noopener" class="link-offset-2 link-primary text-decoration-none fw-semibold"><?= e($company['website']) ?></a>
                                        <?php else: ?>
                                            <span class="text-muted">Belirtilmemiş</span>
                                        <?php endif; ?>
                                    </dd>

                                    <dt class="col-sm-4">Son güncelleme</dt>
                                    <dd class="col-sm-8"><?= e((new DateTimeImmutable($company['updated_at']))->format('d.m.Y H:i')) ?></dd>

                                    <dt class="col-sm-4">Oluşturma</dt>
                                    <dd class="col-sm-8"><?= e((new DateTimeImmutable($company['created_at']))->format('d.m.Y H:i')) ?></dd>
                                </dl>
                                <button id="deleteCompany" class="btn btn-soft-danger mt-4" data-company-id="<?= (int) $company['id'] ?>">
                                    <i class="bi bi-trash me-2"></i>Şirketi sil
                                </button>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light mb-3" style="width: 64px; height: 64px;">
                                        <i class="bi bi-building-add text-primary" style="font-size: 1.75rem;"></i>
                                    </div>
                                    <h2 class="h5 fw-semibold">Henüz şirket bilgisi eklenmedi</h2>
                                    <p class="text-muted mb-0">Başlamak için yukarıdaki butonu kullanarak şirket bilgilerinizi oluşturun.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xl-6">
                    <div class="card glass-card border-0 <?= $company ? 'd-none' : '' ?> section-card" id="companyFormCard">
                        <div class="card-body">
                            <h2 class="h5 fw-semibold mb-4">Şirket Formu</h2>
                            <form id="companyForm" class="needs-validation" novalidate>
                                <input type="hidden" name="id" value="<?= $company ? (int) $company['id'] : '' ?>">
                                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                <div class="mb-3">
                                    <label class="form-label" for="companyName">Şirket adı <span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" id="companyName" name="name" value="<?= $company ? e($company['name']) : '' ?>" required>
                                    <div class="invalid-feedback">Bu alan zorunludur.</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="companyAddress">Adres</label>
                                    <textarea class="form-control" id="companyAddress" name="adres" rows="3"><?= $company ? e($company['adres'] ?? '') : '' ?></textarea>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label" for="companyPhone">Telefon</label>
                                        <input class="form-control" type="text" id="companyPhone" name="phone" value="<?= $company ? e($company['phone'] ?? '') : '' ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="companyFax">Faks</label>
                                        <input class="form-control" type="text" id="companyFax" name="fax" value="<?= $company ? e($company['fax'] ?? '') : '' ?>">
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <label class="form-label" for="companyWebsite">Web sitesi</label>
                                    <input class="form-control" type="url" id="companyWebsite" name="website" value="<?= $company ? e($company['website'] ?? '') : '' ?>" placeholder="https://">
                                </div>
                                <div class="d-grid d-md-flex gap-2 mt-4">
                                    <button class="btn btn-gradient" type="submit"><i class="bi bi-save me-2"></i>Kaydet</button>
                                    <button class="btn btn-ghost" type="button" id="cancelCompanyForm">İptal</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
(() => {
    const formCard = document.getElementById('companyFormCard');
    const toggleBtn = document.getElementById('toggleCompanyForm');
    const cancelBtn = document.getElementById('cancelCompanyForm');
    const companyForm = document.getElementById('companyForm');
    const deleteBtn = document.getElementById('deleteCompany');
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

    function hideForm() {
        if (!companyForm || !formCard) {
            return;
        }
        if (!companyForm.querySelector('input[name="id"]').value) {
            companyForm.reset();
        }
        formCard.classList.add('d-none');
    }

    if (toggleBtn && formCard) {
        toggleBtn.addEventListener('click', () => {
            formCard.classList.toggle('d-none');
            if (!formCard.classList.contains('d-none')) {
                formCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    }

    if (cancelBtn && formCard) {
        cancelBtn.addEventListener('click', hideForm);
    }

    if (companyForm) {
        companyForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (!companyForm.checkValidity()) {
                companyForm.classList.add('was-validated');
                return;
            }

            const formData = new FormData(companyForm);
            const hasId = Boolean(formData.get('id'));
            const endpoint = hasId ? 'api/company/edit.php' : 'api/company/add.php';

            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-Token': csrfToken,
                    },
                    body: formData,
                });

                const payload = await response.json();

                if (!response.ok || !payload.success) {
                    throw new Error(payload.message || 'İşlem sırasında bir hata oluştu.');
                }

                window.location.reload();
            } catch (error) {
                alert(error.message);
            }
        });
    }

    if (deleteBtn) {
        deleteBtn.addEventListener('click', async () => {
            const companyId = deleteBtn.getAttribute('data-company-id');
            if (!companyId) {
                return;
            }

            if (!confirm('Şirket kaydını silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.')) {
                return;
            }

            const formData = new FormData();
            formData.set('id', companyId);
            formData.set('csrf_token', csrfToken);

            try {
                const response = await fetch('api/company/delete.php', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-Token': csrfToken,
                    },
                    body: formData,
                });

                const payload = await response.json();

                if (!response.ok || !payload.success) {
                    throw new Error(payload.message || 'Şirket silinirken bir hata oluştu.');
                }

                window.location.reload();
            } catch (error) {
                alert(error.message);
            }
        });
    }
})();
</script>
</body>
</html>
