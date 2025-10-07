<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$user = require_login();
$pdo = get_db_connection();

$companyStmt = $pdo->prepare('SELECT id, name FROM company WHERE user_id = :user_id LIMIT 1');
$companyStmt->execute([':user_id' => $user['id']]);
$company = $companyStmt->fetch() ?: null;

$contacts = [];
if ($company) {
    $contactsStmt = $pdo->prepare('SELECT id, ad, gorev, telefon, eposta, aktif, created_at, updated_at FROM company_contacts WHERE company_id = :company_id ORDER BY ad ASC');
    $contactsStmt->execute([':company_id' => $company['id']]);
    $contacts = $contactsStmt->fetchAll();
}

$pageTitle = 'Şirket Çalışanları - Nexa';
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
            font-size: 0.95rem;
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
            font-size: clamp(1.4rem, 2.2vw, 2rem);
            font-weight: 700;
        }

        .welcome-card p {
            font-size: 0.95rem;
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
            font-size: 0.8rem;
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
            font-size: 0.95rem;
        }

        .btn-gradient:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            box-shadow: none;
        }

        .btn-gradient:hover:not(:disabled) {
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
            font-size: 0.95rem;
        }

        .btn-ghost:hover {
            background: rgba(255, 255, 255, 1);
            color: #111827;
            transform: translateY(-1px);
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.12);
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

        .info-alert {
            padding: 2rem;
            display: flex;
            align-items: flex-start;
            gap: 1.25rem;
            font-size: 0.95rem;
        }

        .info-alert .icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.45);
            color: #4338ca;
            font-size: 1.75rem;
        }

        .info-alert h2 {
            margin-bottom: 0.5rem;
        }

        .info-alert .h5 {
            font-size: 1.1rem;
        }

        .table thead th {
            font-size: 0.75rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #6b7280;
            border-bottom: none;
        }

        .table tbody tr {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .table tbody tr:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(15, 23, 42, 0.08);
        }

        .table tbody td {
            border-top: none;
            padding: 1.15rem 1rem;
            font-size: 0.95rem;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.45rem 0.9rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-pill-success {
            background: rgba(17, 153, 142, 0.12);
            color: #047857;
        }

        .status-pill-muted {
            background: rgba(148, 163, 184, 0.18);
            color: #475569;
        }

        .contact-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
        }

        .btn-icon {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-icon:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.12);
        }

        .btn-icon-secondary {
            background: rgba(59, 130, 246, 0.12);
            color: #2563eb;
        }

        .btn-icon-secondary:hover {
            color: #1d4ed8;
        }

        .btn-icon-danger {
            background: rgba(239, 68, 68, 0.12);
            color: #b91c1c;
        }

        .btn-icon-danger:hover {
            color: #991b1b;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 1.5rem;
        }

        .empty-state i {
            font-size: 3rem;
            color: #c7d2fe;
        }

        .empty-state .h5 {
            font-size: 1.1rem;
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

        #contactModal .modal-content {
            border-radius: 24px;
            border: none;
            box-shadow: 0 25px 60px rgba(15, 23, 42, 0.35);
        }

        #contactModal .modal-header {
            background: var(--primary-gradient);
            color: #fff;
            border-bottom: none;
            border-top-left-radius: 24px;
            border-top-right-radius: 24px;
        }

        #contactModal .modal-header .btn-close {
            filter: invert(1);
        }

        #contactModal .modal-footer {
            border-top: none;
            padding: 1.5rem 2rem;
        }

        #contactModal .modal-body {
            padding: 2rem;
        }

        @media (max-width: 767.98px) {
            .welcome-card {
                padding: 2rem;
            }

            .section-card .card-body {
                padding: 1.5rem;
            }

            .info-alert {
                flex-direction: column;
                align-items: flex-start;
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
                            <i class="bi bi-people"></i>
                            Şirket Çalışanları
                        </span>
                        <h1 class="mb-3">Şirket ağınızı kolayca yönetin</h1>
                        <p class="mb-0 opacity-75">Takım arkadaşlarınızı ve paydaşlarınızı tek bir yerden takip edin. Kişileri güncelleyin, yeni kişiler ekleyin ve iletişim bilgilerini paylaşın.</p>
                        <?php if ($company): ?>
                            <div class="meta-badges">
                                <span class="meta-badge"><i class="bi bi-building"></i><?= e($company['name']) ?></span>
                                <span class="meta-badge"><i class="bi bi-person-lines-fill"></i>Kayıtlı kişi: <?= count($contacts) ?></span>
                            </div>
                        <?php else: ?>
                            <div class="meta-badges">
                                <span class="meta-badge"><i class="bi bi-info-circle"></i>Önce şirket bilgisi oluşturun</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex flex-column flex-sm-row gap-2">
                        <button id="addContactBtn" class="btn btn-gradient" <?= $company ? '' : 'disabled' ?>>
                            <i class="bi bi-person-plus-fill me-2"></i>Yeni kişi ekle
                        </button>
                        <button class="btn btn-ghost" type="button" onclick="window.location.href='company.php'">
                            <i class="bi bi-building-gear me-2"></i>Şirket kartı
                        </button>
                    </div>
                </div>
            </div>

            <?php if (!$company): ?>
                <div class="glass-card info-alert">
                    <div class="icon"><i class="bi bi-info-circle"></i></div>
                    <div>
                        <h2 class="h5 fw-semibold">Şirket kaydı bulunamadı</h2>
                        <p class="mb-0">Yeni bir kişi ekleyebilmek için önce <a href="company.php" class="fw-semibold text-decoration-none">şirket bilgilerinizi oluşturmalısınız</a>. Formu tamamladıktan sonra kişileri yönetebilirsiniz.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="card glass-card border-0 section-card">
                    <div class="card-body">
                        <?php if (empty($contacts)): ?>
                            <div class="empty-state">
                                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-white shadow-sm" style="width: 80px; height: 80px;">
                                    <i class="bi bi-people"></i>
                                </div>
                                <h2 class="h5 fw-semibold mt-3">Henüz kişi eklenmemiş</h2>
                                <p class="text-muted mb-0">Yeni bir kişi eklemek için yukarıdaki butonu kullanın.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th scope="col">Ad</th>
                                            <th scope="col">Görev</th>
                                            <th scope="col">Telefon</th>
                                            <th scope="col">E-posta</th>
                                            <th scope="col">Durum</th>
                                            <th scope="col" class="text-end">İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($contacts as $contact): ?>
                                            <tr>
                                                <td class="fw-semibold"><?= e($contact['ad'] ?? '') ?></td>
                                                <td><?= $contact['gorev'] ? e($contact['gorev']) : '<span class="text-muted">Belirtilmemiş</span>' ?></td>
                                                <td><?= $contact['telefon'] ? e($contact['telefon']) : '<span class="text-muted">Belirtilmemiş</span>' ?></td>
                                                <td>
                                                    <?php if ($contact['eposta']): ?>
                                                        <a href="mailto:<?= e($contact['eposta']) ?>" class="text-decoration-none fw-semibold link-primary"><?= e($contact['eposta']) ?></a>
                                                    <?php else: ?>
                                                        <span class="text-muted">Belirtilmemiş</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ((int) $contact['aktif'] === 1): ?>
                                                        <span class="status-pill status-pill-success"><i class="bi bi-patch-check-fill"></i>Aktif</span>
                                                    <?php else: ?>
                                                        <span class="status-pill status-pill-muted"><i class="bi bi-pause-circle"></i>Pasif</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="contact-actions">
                                                    <button type="button" class="btn-icon btn-icon-secondary" data-edit-contact
                                                            data-id="<?= (int) $contact['id'] ?>"
                                                            data-ad="<?= e($contact['ad'] ?? '') ?>"
                                                            data-gorev="<?= e($contact['gorev'] ?? '') ?>"
                                                            data-telefon="<?= e($contact['telefon'] ?? '') ?>"
                                                            data-eposta="<?= e($contact['eposta'] ?? '') ?>"
                                                            data-aktif="<?= (int) $contact['aktif'] ?>">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button type="button" class="btn-icon btn-icon-danger" data-delete-contact data-id="<?= (int) $contact['id'] ?>">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title h5 fw-semibold" id="contactModalLabel">Yeni kişi ekle</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <form id="contactForm" class="needs-validation" novalidate>
                <div class="modal-body">
                    <input type="hidden" name="id" id="contactId">
                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                    <div class="mb-3">
                        <label class="form-label" for="contactName">Ad <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="contactName" name="ad" required>
                        <div class="invalid-feedback">Bu alan zorunludur.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="contactRole">Görev</label>
                        <input type="text" class="form-control" id="contactRole" name="gorev">
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="contactPhone">Telefon</label>
                            <input type="text" class="form-control" id="contactPhone" name="telefon">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="contactEmail">E-posta</label>
                            <input type="email" class="form-control" id="contactEmail" name="eposta">
                            <div class="invalid-feedback">Lütfen geçerli bir e-posta adresi giriniz.</div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label" for="contactStatus">Durum</label>
                        <select class="form-select" id="contactStatus" name="aktif">
                            <option value="1" selected>Aktif</option>
                            <option value="0">Pasif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-gradient" id="contactSubmit">
                        <i class="bi bi-save me-2"></i>Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
(() => {
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
    const contactModalEl = document.getElementById('contactModal');
    const contactForm = document.getElementById('contactForm');
    const modalTitle = document.getElementById('contactModalLabel');
    const contactIdInput = document.getElementById('contactId');
    const contactNameInput = document.getElementById('contactName');
    const contactRoleInput = document.getElementById('contactRole');
    const contactPhoneInput = document.getElementById('contactPhone');
    const contactEmailInput = document.getElementById('contactEmail');
    const contactStatusSelect = document.getElementById('contactStatus');
    const addContactBtn = document.getElementById('addContactBtn');
    const deleteButtons = document.querySelectorAll('[data-delete-contact]');
    const editButtons = document.querySelectorAll('[data-edit-contact]');
    const modal = contactModalEl ? new bootstrap.Modal(contactModalEl) : null;

    function resetForm() {
        if (!contactForm) {
            return;
        }
        contactForm.reset();
        contactForm.classList.remove('was-validated');
        contactIdInput.value = '';
        contactStatusSelect.value = '1';
    }

    function openForCreate() {
        resetForm();
        if (modalTitle) {
            modalTitle.textContent = 'Yeni kişi ekle';
        }
        if (modal) {
            modal.show();
        }
    }

    function openForEdit(button) {
        resetForm();
        contactIdInput.value = button.getAttribute('data-id') || '';
        contactNameInput.value = button.getAttribute('data-ad') || '';
        contactRoleInput.value = button.getAttribute('data-gorev') || '';
        contactPhoneInput.value = button.getAttribute('data-telefon') || '';
        contactEmailInput.value = button.getAttribute('data-eposta') || '';
        contactStatusSelect.value = button.getAttribute('data-aktif') === '0' ? '0' : '1';
        if (modalTitle) {
            modalTitle.textContent = 'Kişiyi düzenle';
        }
        if (modal) {
            modal.show();
        }
    }

    if (addContactBtn) {
        addContactBtn.addEventListener('click', () => {
            openForCreate();
        });
    }

    editButtons.forEach((button) => {
        button.addEventListener('click', () => {
            openForEdit(button);
        });
    });

    if (contactForm) {
        contactForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            if (!contactForm.checkValidity()) {
                contactForm.classList.add('was-validated');
                return;
            }

            const formData = new FormData(contactForm);
            const hasId = Boolean(formData.get('id'));
            const endpoint = hasId ? 'api/company_contact/edit.php' : 'api/company_contact/add.php';

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

    deleteButtons.forEach((button) => {
        button.addEventListener('click', async () => {
            const contactId = button.getAttribute('data-id');
            if (!contactId) {
                return;
            }

            if (!confirm('Bu kişiyi silmek istediğinize emin misiniz? Bu işlem geri alınamaz.')) {
                return;
            }

            const formData = new FormData();
            formData.set('id', contactId);
            formData.set('csrf_token', csrfToken);

            try {
                const response = await fetch('api/company_contact/delete.php', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-Token': csrfToken,
                    },
                    body: formData,
                });

                const payload = await response.json();

                if (!response.ok || !payload.success) {
                    throw new Error(payload.message || 'Kişi silinirken bir hata oluştu.');
                }

                window.location.reload();
            } catch (error) {
                alert(error.message);
            }
        });
    });
})();
</script>
</body>
</html>
