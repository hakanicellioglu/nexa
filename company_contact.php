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

$pageTitle = 'İletişim Kişileri - Nexa';
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
        .table thead th {
            font-size: 0.8rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .contact-actions button {
            padding: 0.25rem 0.5rem;
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
                    <h1 class="h3 fw-semibold mb-1">İletişim Kişileri</h1>
                    <p class="text-muted mb-0">Şirketinizle ilişkili kişileri yönetin ve iletişim bilgilerini güncel tutun.</p>
                </div>
                <button id="addContactBtn" class="btn btn-primary" <?= $company ? '' : 'disabled' ?>>
                    <i class="bi bi-person-plus-fill me-2"></i>Yeni kişi ekle
                </button>
            </div>

            <?php if (!$company): ?>
                <div class="alert alert-info" role="alert">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-info-circle fs-4"></i>
                        <div>
                            <h2 class="h6 fw-semibold mb-1">Şirket kaydı bulunamadı</h2>
                            <p class="mb-0 small">Kişi ekleyebilmek için önce <a href="company.php" class="fw-semibold">şirket bilgilerinizi oluşturmalısınız</a>.</p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <?php if (empty($contacts)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
                                <h2 class="h5 fw-semibold mt-3">Henüz kişi eklenmemiş</h2>
                                <p class="text-muted mb-0">Yeni bir kişi eklemek için sağ üstteki butonu kullanın.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead class="text-muted small">
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
                                                        <a href="mailto:<?= e($contact['eposta']) ?>"><?= e($contact['eposta']) ?></a>
                                                    <?php else: ?>
                                                        <span class="text-muted">Belirtilmemiş</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ((int) $contact['aktif'] === 1): ?>
                                                        <span class="badge bg-success-subtle border border-success-subtle text-success-emphasis">Aktif</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary-subtle border border-secondary-subtle text-secondary-emphasis">Pasif</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end contact-actions">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm me-1" data-edit-contact
                                                            data-id="<?= (int) $contact['id'] ?>"
                                                            data-ad="<?= e($contact['ad'] ?? '') ?>"
                                                            data-gorev="<?= e($contact['gorev'] ?? '') ?>"
                                                            data-telefon="<?= e($contact['telefon'] ?? '') ?>"
                                                            data-eposta="<?= e($contact['eposta'] ?? '') ?>"
                                                            data-aktif="<?= (int) $contact['aktif'] ?>">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger btn-sm" data-delete-contact data-id="<?= (int) $contact['id'] ?>">
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
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary" id="contactSubmit">
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
