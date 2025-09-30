<?php
session_start();

if (! isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/config.php';

$suppliers = [];

$query = 'SELECT id, name, created_at, updated_at FROM suppliers ORDER BY created_at DESC';
if ($result = $mysqli->query($query)) {
    while ($row = $result->fetch_assoc()) {
        $suppliers[] = $row;
    }
    $result->free();
}

$pageTitle = 'Tedarikçiler';
require_once __DIR__ . '/header.php';

function formatDate(?string $date): string
{
    if ($date === null || $date === '') {
        return '-';
    }

    try {
        $dateTime = new DateTime($date);
        return $dateTime->format('d.m.Y H:i');
    } catch (Exception $e) {
        return '-';
    }
}
?>
        <style>
            .suppliers-wrapper {
                background: #fff;
                border-radius: 12px;
                padding: 24px;
                box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
            }

            .suppliers-wrapper h2 {
                margin: 0;
                font-size: 1.35rem;
            }

            .list-header {
                display: flex;
                flex-direction: column;
                gap: 4px;
            }

            .table-wrapper {
                margin-top: 20px;
                overflow-x: auto;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            th,
            td {
                padding: 12px 16px;
                text-align: left;
                border-bottom: 1px solid #e5e7eb;
                vertical-align: middle;
            }

            th {
                font-size: 0.9rem;
                text-transform: uppercase;
                letter-spacing: 0.03em;
                color: #6b7280;
            }

            tbody tr[data-id]:hover {
                background: #f3f4f6;
            }

            input[type="text"] {
                width: 100%;
                padding: 10px 12px;
                border: 1px solid #d1d5db;
                border-radius: 6px;
                font-size: 1rem;
                transition: border-color 0.2s ease;
            }

            input[type="text"]:focus {
                border-color: #2563eb;
                outline: none;
                box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
            }

            button {
                cursor: pointer;
            }

            .primary-button,
            .secondary-button,
            .danger-button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 10px 18px;
                border-radius: 6px;
                border: none;
                font-weight: 600;
                transition: background 0.2s ease, transform 0.2s ease;
            }

            .primary-button {
                background: #2563eb;
                color: #fff;
            }

            .primary-button:hover {
                background: #1d4ed8;
            }

            .secondary-button {
                background: #e5e7eb;
                color: #1f2937;
            }

            .secondary-button:hover {
                background: #d1d5db;
            }

            .danger-button {
                background: #dc2626;
                color: #fff;
            }

            .danger-button:hover {
                background: #b91c1c;
            }

            .button-group {
                display: flex;
                gap: 8px;
            }

            .status-message {
                font-size: 0.95rem;
                min-height: 1.2em;
            }

            .status-message.success {
                color: #047857;
            }

            .status-message.error {
                color: #b91c1c;
            }

            .add-row-label {
                font-weight: 600;
                color: #2563eb;
                white-space: nowrap;
            }

            .add-row-actions {
                display: flex;
                align-items: center;
                gap: 12px;
            }

            .modal-backdrop {
                position: fixed;
                inset: 0;
                background: rgba(15, 23, 42, 0.45);
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 24px;
                z-index: 1000;
            }

            .modal {
                background: #fff;
                border-radius: 12px;
                padding: 24px;
                max-width: 420px;
                width: 100%;
                box-shadow: 0 20px 45px rgba(15, 23, 42, 0.18);
                position: relative;
            }

            .modal h3 {
                margin-top: 0;
                font-size: 1.2rem;
            }

            .modal-actions {
                display: flex;
                justify-content: flex-end;
                gap: 12px;
                margin-top: 24px;
            }

            .hidden {
                display: none !important;
            }

            .empty-state {
                padding: 32px 16px;
                text-align: center;
                color: #6b7280;
            }
        </style>
        <section class="suppliers-wrapper">
            <div class="list-header">
                <h2>Tedarikçi Listesi</h2>
                <p class="status-message" id="list-status"></p>
            </div>
            <div class="table-wrapper">
                <table id="supplier-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Ad</th>
                            <th>Oluşturulma</th>
                            <th>Güncellenme</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr id="add-row">
                            <td class="add-row-label">Yeni</td>
                            <td>
                                <form id="supplier-add-form" autocomplete="off">
                                    <input type="text" id="supplier-name" name="name" placeholder="Örn. Nexa Cam" required>
                                </form>
                            </td>
                            <td colspan="2">
                                <span class="status-message" id="add-status"></span>
                            </td>
                            <td class="add-row-actions">
                                <button type="submit" class="primary-button" form="supplier-add-form">Ekle</button>
                            </td>
                        </tr>
                        <?php if (count($suppliers) === 0) : ?>
                            <tr data-empty-row>
                                <td colspan="5">
                                    <div class="empty-state">Henüz tedarikçi eklenmemiş.</div>
                                </td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($suppliers as $index => $supplier) : ?>
                                <tr data-id="<?php echo (int) $supplier['id']; ?>">
                                    <td><?php echo $index + 1; ?></td>
                                    <td class="supplier-name"><?php echo htmlspecialchars($supplier['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="supplier-created">
                                        <?php echo htmlspecialchars(formatDate($supplier['created_at']), ENT_QUOTES, 'UTF-8'); ?>
                                    </td>
                                    <td class="supplier-updated">
                                        <?php echo htmlspecialchars(formatDate($supplier['updated_at']), ENT_QUOTES, 'UTF-8'); ?>
                                    </td>
                                    <td>
                                        <div class="button-group">
                                            <button type="button" class="secondary-button edit-button">Düzenle</button>
                                            <button type="button" class="danger-button delete-button">Sil</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <div class="modal-backdrop hidden" id="edit-modal">
            <div class="modal">
                <h3>Tedarikçi Düzenle</h3>
                <form id="supplier-edit-form">
                    <input type="hidden" name="id" id="edit-supplier-id">
                    <div class="form-group">
                        <label for="edit-supplier-name">Tedarikçi Adı</label>
                        <input type="text" id="edit-supplier-name" name="name" required>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="secondary-button" data-dismiss>Vazgeç</button>
                        <button type="submit" class="primary-button">Güncelle</button>
                    </div>
                    <div class="status-message" id="edit-status"></div>
                </form>
            </div>
        </div>

        <div class="modal-backdrop hidden" id="delete-modal">
            <div class="modal">
                <h3>Tedarikçiyi Sil</h3>
                <p>Bu tedarikçiyi silmek istediğinize emin misiniz? Bu işlem geri alınamaz.</p>
                <div class="modal-actions">
                    <button type="button" class="secondary-button" data-dismiss>İptal</button>
                    <button type="button" class="danger-button" id="confirm-delete-button">Sil</button>
                </div>
                <div class="status-message" id="delete-status"></div>
            </div>
        </div>

        <script>
            const formatDateForDisplay = (dateString) => {
                if (! dateString) {
                    return '';
                }

                const parsedString = dateString.replace(' ', 'T');
                const date = new Date(parsedString);

                if (Number.isNaN(date.getTime())) {
                    return dateString;
                }

                return date.toLocaleString('tr-TR', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                });
            };

            const addForm = document.getElementById('supplier-add-form');
            const editForm = document.getElementById('supplier-edit-form');
            const addStatus = document.getElementById('add-status');
            const editStatus = document.getElementById('edit-status');
            const deleteStatus = document.getElementById('delete-status');
            const supplierTableBody = document.querySelector('#supplier-table tbody');
            const addRow = document.getElementById('add-row');
            const supplierNameInput = document.getElementById('supplier-name');
            const editModal = document.getElementById('edit-modal');
            const deleteModal = document.getElementById('delete-modal');
            const editSupplierId = document.getElementById('edit-supplier-id');
            const editSupplierName = document.getElementById('edit-supplier-name');
            const confirmDeleteButton = document.getElementById('confirm-delete-button');
            let supplierIdToDelete = null;

            const showMessage = (element, message, type = '') => {
                element.textContent = message;
                element.classList.remove('success', 'error');
                if (type) {
                    element.classList.add(type);
                }
            };

            const clearMessage = (element) => {
                element.textContent = '';
                element.classList.remove('success', 'error');
            };

            const openModal = (modal) => {
                modal.classList.remove('hidden');
            };

            const closeModal = (modal) => {
                modal.classList.add('hidden');
                const statusElement = modal.querySelector('.status-message');
                if (statusElement) {
                    clearMessage(statusElement);
                }
            };

            document.querySelectorAll('[data-dismiss]').forEach((button) => {
                button.addEventListener('click', () => {
                    closeModal(button.closest('.modal-backdrop'));
                });
            });

            const createRow = (supplier, index = null) => {
                const tr = document.createElement('tr');
                tr.dataset.id = supplier.id;

                const indexCell = document.createElement('td');
                const currentCount = supplierTableBody.querySelectorAll('tr[data-id]').length;
                indexCell.textContent = index !== null ? index : currentCount + 1;

                const nameCell = document.createElement('td');
                nameCell.className = 'supplier-name';
                nameCell.textContent = supplier.name;

                const createdCell = document.createElement('td');
                createdCell.className = 'supplier-created';
                createdCell.textContent = formatDateForDisplay(supplier.created_at);

                const updatedCell = document.createElement('td');
                updatedCell.className = 'supplier-updated';
                updatedCell.textContent = formatDateForDisplay(supplier.updated_at);

                const actionsCell = document.createElement('td');
                const buttonGroup = document.createElement('div');
                buttonGroup.className = 'button-group';

                const editButton = document.createElement('button');
                editButton.type = 'button';
                editButton.className = 'secondary-button edit-button';
                editButton.textContent = 'Düzenle';

                const deleteButton = document.createElement('button');
                deleteButton.type = 'button';
                deleteButton.className = 'danger-button delete-button';
                deleteButton.textContent = 'Sil';

                buttonGroup.append(editButton, deleteButton);
                actionsCell.append(buttonGroup);

                tr.append(indexCell, nameCell, createdCell, updatedCell, actionsCell);

                return tr;
            };

            const refreshRowIndexes = () => {
                supplierTableBody.querySelectorAll('tr[data-id]').forEach((row, rowIndex) => {
                    const indexCell = row.querySelector('td');
                    if (indexCell) {
                        indexCell.textContent = rowIndex + 1;
                    }
                });
            };

            const removeEmptyState = () => {
                const emptyRow = supplierTableBody.querySelector('[data-empty-row]');
                if (emptyRow) {
                    supplierTableBody.removeChild(emptyRow);
                }
            };

            const openEditModal = (row) => {
                const supplierId = row.dataset.id;
                const supplierName = row.querySelector('.supplier-name').textContent.trim();

                editSupplierId.value = supplierId;
                editSupplierName.value = supplierName;
                openModal(editModal);
            };

            const openDeleteModal = (row) => {
                supplierIdToDelete = row.dataset.id;
                openModal(deleteModal);
            };

            addForm.addEventListener('submit', async (event) => {
                event.preventDefault();
                clearMessage(addStatus);

                const formData = new FormData(addForm);
                const name = String(formData.get('name') || '').trim();

                if (! name) {
                    showMessage(addStatus, 'Lütfen bir tedarikçi adı girin.', 'error');
                    return;
                }

                try {
                    const response = await fetch('api/supplier/add.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ name }),
                    });

                    const data = await response.json();

                    if (! response.ok || ! data.success) {
                        throw new Error(data.message || 'Tedarikçi eklenirken bir hata oluştu.');
                    }

                    showMessage(addStatus, data.message, 'success');

                    removeEmptyState();
                    const newRow = createRow(data.supplier, 1);
                    supplierTableBody.insertBefore(newRow, addRow.nextElementSibling);
                    refreshRowIndexes();
                    addForm.reset();
                    supplierNameInput.focus();
                } catch (error) {
                    showMessage(addStatus, error.message, 'error');
                }
            });

            editForm.addEventListener('submit', async (event) => {
                event.preventDefault();
                clearMessage(editStatus);

                const id = editSupplierId.value;
                const name = editSupplierName.value.trim();

                if (! name) {
                    showMessage(editStatus, 'Lütfen bir tedarikçi adı girin.', 'error');
                    return;
                }

                try {
                    const response = await fetch('api/supplier/edit.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ id, name }),
                    });

                    const data = await response.json();

                    if (! response.ok || ! data.success) {
                        throw new Error(data.message || 'Tedarikçi güncellenirken bir hata oluştu.');
                    }

                    const row = supplierTableBody.querySelector(`tr[data-id="${CSS.escape(id)}"]`);
                    if (row) {
                        row.querySelector('.supplier-name').textContent = data.supplier.name;
                        row.querySelector('.supplier-updated').textContent = formatDateForDisplay(data.supplier.updated_at);
                    }

                    showMessage(editStatus, data.message, 'success');
                    setTimeout(() => closeModal(editModal), 800);
                } catch (error) {
                    showMessage(editStatus, error.message, 'error');
                }
            });

            confirmDeleteButton.addEventListener('click', async () => {
                if (! supplierIdToDelete) {
                    return;
                }

                clearMessage(deleteStatus);

                try {
                    const response = await fetch('api/supplier/delete.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ id: supplierIdToDelete }),
                    });

                    const data = await response.json();

                    if (! response.ok || ! data.success) {
                        throw new Error(data.message || 'Tedarikçi silinirken bir hata oluştu.');
                    }

                    const row = supplierTableBody.querySelector(`tr[data-id="${CSS.escape(supplierIdToDelete)}"]`);
                    if (row) {
                        supplierTableBody.removeChild(row);
                    }

                    showMessage(deleteStatus, data.message, 'success');
                    refreshRowIndexes();

                    setTimeout(() => {
                        closeModal(deleteModal);
                        supplierIdToDelete = null;
                        clearMessage(deleteStatus);

                        if (supplierTableBody.querySelectorAll('tr[data-id]').length === 0) {
                            const emptyRow = document.createElement('tr');
                            emptyRow.setAttribute('data-empty-row', '');
                            const emptyCell = document.createElement('td');
                            emptyCell.colSpan = 5;
                            emptyCell.innerHTML = '<div class="empty-state">Henüz tedarikçi eklenmemiş.</div>';
                            emptyRow.append(emptyCell);
                            supplierTableBody.append(emptyRow);
                        }
                    }, 600);
                } catch (error) {
                    showMessage(deleteStatus, error.message, 'error');
                }
            });

            supplierTableBody.addEventListener('click', (event) => {
                const target = event.target;
                if (target.classList.contains('edit-button')) {
                    const row = target.closest('tr');
                    if (row) {
                        openEditModal(row);
                    }
                }

                if (target.classList.contains('delete-button')) {
                    const row = target.closest('tr');
                    if (row) {
                        openDeleteModal(row);
                    }
                }
            });

            document.querySelectorAll('.supplier-created').forEach((cell) => {
                cell.textContent = formatDateForDisplay(cell.textContent.trim());
            });

            document.querySelectorAll('.supplier-updated').forEach((cell) => {
                cell.textContent = formatDateForDisplay(cell.textContent.trim());
            });
        </script>
    </main>
</body>
</html>
