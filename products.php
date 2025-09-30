<?php
session_start();

if (! isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/config.php';

$products = [];
$productQuery = $mysqli->query('SELECT id, name, type, created_at, updated_at FROM products ORDER BY created_at DESC');
if ($productQuery) {
    while ($row = $productQuery->fetch_assoc()) {
        $products[] = $row;
    }
    $productQuery->free();
}

$pageTitle = 'Ürünler';
require_once __DIR__ . '/header.php';
?>
        <style>
            .product-card {
                display: flex;
                flex-direction: column;
                gap: 24px;
            }

            .product-card h2 {
                margin: 0 0 8px;
                font-size: 1.5rem;
                color: #111827;
            }

            .product-card p {
                margin: 0;
                color: #4b5563;
            }

            .product-card .card-header {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                gap: 24px;
            }

            .product-card .card-header button {
                background: #2563eb;
                border: none;
                color: #fff;
                padding: 10px 20px;
                border-radius: 8px;
                cursor: pointer;
                font-weight: 600;
                transition: background 0.2s ease, transform 0.2s ease;
            }

            .product-card .card-header button:hover {
                background: #1d4ed8;
                transform: translateY(-1px);
            }

            .table-wrapper {
                overflow-x: auto;
            }

            table.product-table {
                width: 100%;
                border-collapse: collapse;
            }

            table.product-table th,
            table.product-table td {
                padding: 12px 16px;
                text-align: left;
                border-bottom: 1px solid #e5e7eb;
            }

            table.product-table thead {
                background: #f3f4f6;
            }

            table.product-table tbody tr:hover {
                background: #f9fafb;
            }

            .actions {
                display: flex;
                gap: 8px;
            }

            .actions button {
                padding: 8px 14px;
                border-radius: 6px;
                border: none;
                cursor: pointer;
                font-weight: 600;
                transition: transform 0.2s ease, box-shadow 0.2s ease;
            }

            .actions button:hover {
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
            }

            .actions .edit {
                background: #f59e0b;
                color: #fff;
            }

            .actions .delete {
                background: #dc2626;
                color: #fff;
            }

            .feedback {
                padding: 12px 16px;
                border-radius: 8px;
                font-weight: 600;
                display: none;
            }

            .feedback.success {
                background: #dcfce7;
                color: #166534;
            }

            .feedback.error {
                background: #fee2e2;
                color: #991b1b;
            }

            .empty-state {
                text-align: center;
                padding: 32px 16px;
                color: #6b7280;
            }

            .modal-overlay {
                position: fixed;
                inset: 0;
                background: rgba(17, 24, 39, 0.55);
                display: none;
                align-items: center;
                justify-content: center;
                padding: 16px;
                z-index: 50;
            }

            .modal {
                background: #fff;
                border-radius: 16px;
                box-shadow: 0 25px 50px -12px rgba(30, 64, 175, 0.35);
                max-width: 420px;
                width: 100%;
                padding: 24px;
                display: flex;
                flex-direction: column;
                gap: 16px;
                animation: fadeIn 0.25s ease;
            }

            .modal h3 {
                margin: 0;
                font-size: 1.25rem;
                color: #111827;
            }

            .modal label {
                display: flex;
                flex-direction: column;
                gap: 6px;
                color: #374151;
                font-weight: 600;
            }

            .modal input,
            .modal select {
                padding: 10px 12px;
                border-radius: 8px;
                border: 1px solid #d1d5db;
                font-size: 1rem;
            }

            .modal input:focus,
            .modal select:focus {
                outline: none;
                border-color: #2563eb;
                box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
            }

            .modal-actions {
                display: flex;
                justify-content: flex-end;
                gap: 12px;
                margin-top: 8px;
            }

            .modal-actions button {
                padding: 10px 18px;
                border-radius: 8px;
                border: none;
                cursor: pointer;
                font-weight: 600;
            }

            .modal-actions .cancel {
                background: #f3f4f6;
                color: #374151;
            }

            .modal-actions .submit {
                background: #2563eb;
                color: #fff;
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translateY(12px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @media (max-width: 640px) {
                .product-card .card-header {
                    flex-direction: column;
                    align-items: stretch;
                }

                .product-card .card-header button {
                    width: 100%;
                }

                .actions {
                    flex-wrap: wrap;
                }
            }
        </style>
        <section class="card product-card">
            <div class="card-header">
                <div>
                    <h2>Ürün Yönetimi</h2>
                    <p>Ürünlerinizi ekleyin, güncelleyin ve silin. Değişiklikler tüm ekip için anında senkronize edilir.</p>
                </div>
                <button id="openAddModal" type="button">Yeni Ürün</button>
            </div>
            <div id="feedback" class="feedback" role="alert"></div>
            <div class="table-wrapper">
                <table class="product-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ürün Adı</th>
                            <th>Tür</th>
                            <th>Oluşturulma</th>
                            <th>Güncellenme</th>
                            <th style="width: 160px;">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody id="productTableBody"></tbody>
                </table>
            </div>
            <div id="emptyState" class="empty-state" style="display: none;">
                Henüz ürün bulunmuyor. İlk ürününüzü ekleyerek başlayın.
            </div>
        </section>

        <div id="productModal" class="modal-overlay" role="dialog" aria-modal="true" aria-hidden="true">
            <div class="modal">
                <h3 id="modalTitle">Yeni Ürün</h3>
                <form id="productForm">
                    <input type="hidden" name="productId" id="productId">
                    <label for="productName">
                        Ürün Adı
                        <input type="text" id="productName" name="name" required>
                    </label>
                    <label for="productType">
                        Ürün Türü
                        <select id="productType" name="type">
                            <option value="">Tür seçin</option>
                            <option value="Isıcam">Isıcam</option>
                            <option value="Tekcam">Tekcam</option>
                        </select>
                    </label>
                    <div class="modal-actions">
                        <button type="button" class="cancel" id="cancelModal">Vazgeç</button>
                        <button type="submit" class="submit" id="submitModal">Kaydet</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            const initialProducts = <?php echo json_encode($products, JSON_UNESCAPED_UNICODE); ?>;
            const products = [...initialProducts];

            const tableBody = document.getElementById('productTableBody');
            const emptyState = document.getElementById('emptyState');
            const feedback = document.getElementById('feedback');
            const modalOverlay = document.getElementById('productModal');
            const productForm = document.getElementById('productForm');
            const modalTitle = document.getElementById('modalTitle');
            const productIdField = document.getElementById('productId');
            const productNameField = document.getElementById('productName');
            const productTypeField = document.getElementById('productType');
            const openAddModalButton = document.getElementById('openAddModal');
            const cancelModalButton = document.getElementById('cancelModal');

            function formatDate(value) {
                if (!value) {
                    return '-';
                }
                const date = new Date(value.replace(' ', 'T'));
                if (Number.isNaN(date.getTime())) {
                    return value;
                }
                return date.toLocaleString('tr-TR', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }

            function showFeedback(message, type = 'success') {
                feedback.textContent = message;
                feedback.classList.remove('success', 'error');
                feedback.classList.add(type);
                feedback.style.display = 'block';
                setTimeout(() => {
                    feedback.style.display = 'none';
                }, 3500);
            }

            function toggleModal(show = false) {
                if (show) {
                    modalOverlay.style.display = 'flex';
                    modalOverlay.setAttribute('aria-hidden', 'false');
                    productNameField.focus();
                } else {
                    modalOverlay.style.display = 'none';
                    modalOverlay.setAttribute('aria-hidden', 'true');
                    productForm.reset();
                    productIdField.value = '';
                }
            }

            function renderProducts() {
                tableBody.innerHTML = '';
                if (products.length === 0) {
                    emptyState.style.display = 'block';
                    return;
                }
                emptyState.style.display = 'none';

                products.forEach((product) => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>#${product.id}</td>
                        <td>${product.name ? escapeHtml(product.name) : '-'}</td>
                        <td>${product.type ? escapeHtml(product.type) : '-'}</td>
                        <td>${formatDate(product.created_at)}</td>
                        <td>${formatDate(product.updated_at)}</td>
                        <td>
                            <div class="actions">
                                <button type="button" class="edit" data-id="${product.id}">Düzenle</button>
                                <button type="button" class="delete" data-id="${product.id}">Sil</button>
                            </div>
                        </td>
                    `;
                    tableBody.appendChild(row);
                });
            }

            function escapeHtml(value) {
                const div = document.createElement('div');
                div.textContent = value;
                return div.innerHTML;
            }

            tableBody.addEventListener('click', (event) => {
                const target = event.target;
                if (target.matches('button.edit')) {
                    const id = Number.parseInt(target.dataset.id, 10);
                    const product = products.find((item) => item.id === id);
                    if (!product) {
                        return;
                    }
                    modalTitle.textContent = 'Ürünü Düzenle';
                    productIdField.value = product.id;
                    productNameField.value = product.name || '';
                    productTypeField.value = product.type || '';
                    if (product.type && productTypeField.value !== product.type) {
                        const hasOption = Array.from(productTypeField.options).some((option) => option.value === product.type);
                        if (!hasOption) {
                            const customOption = document.createElement('option');
                            customOption.value = product.type;
                            customOption.textContent = product.type;
                            productTypeField.appendChild(customOption);
                        }
                        productTypeField.value = product.type;
                    }
                    toggleModal(true);
                }

                if (target.matches('button.delete')) {
                    const id = Number.parseInt(target.dataset.id, 10);
                    const product = products.find((item) => item.id === id);
                    if (!product) {
                        return;
                    }
                    const confirmation = confirm(`${product.name || 'Bu ürünü'} silmek istediğinize emin misiniz?`);
                    if (!confirmation) {
                        return;
                    }
                    fetch('api/product/delete.php', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ id }),
                    })
                        .then(async (response) => {
                            const payload = await response.json();
                            if (!response.ok) {
                                throw new Error(payload.message || 'Ürün silinemedi.');
                            }
                            return payload;
                        })
                        .then((payload) => {
                            const index = products.findIndex((item) => item.id === id);
                            if (index !== -1) {
                                products.splice(index, 1);
                                renderProducts();
                            }
                            showFeedback(payload.message, 'success');
                        })
                        .catch((error) => {
                            showFeedback(error.message, 'error');
                        });
                }
            });

            openAddModalButton.addEventListener('click', () => {
                modalTitle.textContent = 'Yeni Ürün';
                toggleModal(true);
            });

            cancelModalButton.addEventListener('click', () => {
                toggleModal(false);
            });

            modalOverlay.addEventListener('click', (event) => {
                if (event.target === modalOverlay) {
                    toggleModal(false);
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && modalOverlay.style.display === 'flex') {
                    toggleModal(false);
                }
            });

            productForm.addEventListener('submit', (event) => {
                event.preventDefault();
                const formData = new FormData(productForm);
                const id = Number.parseInt(formData.get('productId'), 10);
                const name = formData.get('name').trim();
                const type = formData.get('type').trim();

                if (!name) {
                    showFeedback('Lütfen ürün adını girin.', 'error');
                    return;
                }

                const payload = { name, type };
                let url = 'api/product/add.php';
                let method = 'POST';

                if (!Number.isNaN(id) && id > 0) {
                    payload.id = id;
                    url = 'api/product/edit.php';
                    method = 'PUT';
                }

                fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(payload),
                })
                    .then(async (response) => {
                        const responseData = await response.json();
                        if (!response.ok) {
                            throw new Error(responseData.message || 'İşlem tamamlanamadı.');
                        }
                        return responseData;
                    })
                    .then((responseData) => {
                        const product = responseData.data;
                        const existingIndex = products.findIndex((item) => item.id === product.id);
                        if (existingIndex === -1) {
                            products.unshift(product);
                        } else {
                            products.splice(existingIndex, 1, product);
                        }
                        renderProducts();
                        toggleModal(false);
                        showFeedback(responseData.message, 'success');
                    })
                    .catch((error) => {
                        showFeedback(error.message, 'error');
                    });
            });

            renderProducts();
        </script>
    </main>
</body>
</html>
