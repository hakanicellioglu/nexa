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
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f5f6fa;
        }
        .app-layout {
            min-height: 100vh;
        }
        .sidebar {
            background: #111827;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.75);
            font-weight: 500;
        }
        .sidebar .nav-link.active,
        .sidebar .nav-link:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.12);
        }
        .sidebar .nav-link i {
            width: 1.5rem;
        }
        .main-content {
            min-height: 100vh;
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
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between mb-4 gap-3">
                <div>
                    <h1 class="h3 fw-semibold mb-1">Şirket Bilgileri</h1>
                    <p class="text-muted mb-0">Şirket kartınızı güncel tutun. Tüm değişiklikler anında kaydedilir.</p>
                </div>
                <button id="toggleCompanyForm" class="btn btn-primary">
                    <i class="bi bi-pencil-square me-2"></i><?= $company ? 'Bilgileri düzenle' : 'Yeni şirket ekle' ?>
                </button>
            </div>

            <div class="row g-4">
                <div class="col-12 col-xl-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body">
                            <h2 class="h5 fw-semibold mb-3">Mevcut Durum</h2>
                            <?php if ($company): ?>
                                <dl class="row mb-0 small">
                                    <dt class="col-sm-4 text-muted">Şirket adı</dt>
                                    <dd class="col-sm-8 fw-medium"><?= e($company['name']) ?></dd>

                                    <dt class="col-sm-4 text-muted">Adres</dt>
                                    <dd class="col-sm-8"><?= $company['adres'] ? nl2br(e($company['adres'])) : '<span class="text-muted">Belirtilmemiş</span>' ?></dd>

                                    <dt class="col-sm-4 text-muted">Telefon</dt>
                                    <dd class="col-sm-8"><?= $company['phone'] ? e($company['phone']) : '<span class="text-muted">Belirtilmemiş</span>' ?></dd>

                                    <dt class="col-sm-4 text-muted">Faks</dt>
                                    <dd class="col-sm-8"><?= $company['fax'] ? e($company['fax']) : '<span class="text-muted">Belirtilmemiş</span>' ?></dd>

                                    <dt class="col-sm-4 text-muted">Web sitesi</dt>
                                    <dd class="col-sm-8">
                                        <?php if ($company['website']): ?>
                                            <a href="<?= e($company['website']) ?>" target="_blank" rel="noopener"><?= e($company['website']) ?></a>
                                        <?php else: ?>
                                            <span class="text-muted">Belirtilmemiş</span>
                                        <?php endif; ?>
                                    </dd>

                                    <dt class="col-sm-4 text-muted">Son güncelleme</dt>
                                    <dd class="col-sm-8"><?= e((new DateTimeImmutable($company['updated_at']))->format('d.m.Y H:i')) ?></dd>
                                </dl>
                                <button id="deleteCompany" class="btn btn-outline-danger btn-sm mt-4" data-company-id="<?= (int) $company['id'] ?>">
                                    <i class="bi bi-trash me-1"></i>Şirketi sil
                                </button>
                            <?php else: ?>
                                <p class="text-muted mb-0">Henüz herhangi bir şirket bilgisi eklenmemiş. Başlamak için sağ üstteki butondan formu açın.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xl-6">
                    <div class="card shadow-sm border-0 <?= $company ? 'd-none' : '' ?>" id="companyFormCard">
                        <div class="card-body">
                            <h2 class="h5 fw-semibold mb-3">Şirket Formu</h2>
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
                                    <button class="btn btn-primary" type="submit"><i class="bi bi-save me-2"></i>Kaydet</button>
                                    <button class="btn btn-outline-secondary" type="button" id="cancelCompanyForm">İptal</button>
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
