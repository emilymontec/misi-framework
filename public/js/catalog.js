(function () {
    'use strict';

    function escapeHtml(value) {
        var div = document.createElement('div');
        div.textContent = value === null || value === undefined ? '' : String(value);
        return div.innerHTML;
    }

    var categoriesById = {};

    function loadCategories() {
        return api.get('/modules/catalog/categories').then(function (categories) {
            categoriesById = {};
            categories.forEach(function (c) { categoriesById[c.id] = c; });

            var select = document.getElementById('product-category');
            select.innerHTML = '<option value="">Sin categoría</option>' + categories.map(function (c) {
                return '<option value="' + c.id + '">' + escapeHtml(c.name) + '</option>';
            }).join('');

            var tbody = document.getElementById('categories-tbody');
            tbody.innerHTML = categories.length ? '' : '<tr><td colspan="2" class="sd-text-muted">Sin categorías todavía.</td></tr>';
            categories.forEach(function (c) {
                var row = document.createElement('tr');
                row.innerHTML =
                    '<td>' + escapeHtml(c.name) + '</td>' +
                    '<td><button class="sd-btn sd-btn-danger sd-btn-sm" data-delete-category="' + c.id + '">Eliminar</button></td>';
                tbody.appendChild(row);
            });
        });
    }

    function loadProducts() {
        return api.get('/modules/catalog/products').then(function (products) {
            var tbody = document.getElementById('products-tbody');
            tbody.innerHTML = products.length ? '' : '<tr><td colspan="6" class="sd-text-muted">Sin productos todavía.</td></tr>';
            products.forEach(function (p) {
                var row = document.createElement('tr');
                row.innerHTML =
                    '<td>' + escapeHtml(p.sku) + '</td>' +
                    '<td>' + escapeHtml(p.name) + '</td>' +
                    '<td>' + escapeHtml(p.category_name || '—') + '</td>' +
                    '<td>' + escapeHtml(p.price) + '</td>' +
                    '<td>' + escapeHtml(p.stock_quantity) + '</td>' +
                    '<td style="white-space:nowrap;">' +
                        '<button class="sd-btn sd-btn-secondary sd-btn-sm" data-edit-product="' + p.id + '">Editar</button> ' +
                        '<button class="sd-btn sd-btn-secondary sd-btn-sm" data-adjust-product="' + p.id + '" data-delta="1">+1</button> ' +
                        '<button class="sd-btn sd-btn-secondary sd-btn-sm" data-adjust-product="' + p.id + '" data-delta="-1">−1</button> ' +
                        '<button class="sd-btn sd-btn-danger sd-btn-sm" data-delete-product="' + p.id + '">Eliminar</button>' +
                    '</td>';
                tbody.appendChild(row);
            });
        });
    }

    document.getElementById('category-form').addEventListener('submit', function (event) {
        event.preventDefault();
        var name = document.getElementById('category-name').value;
        api.post('/modules/catalog/categories', { name: name })
            .then(function () {
                document.getElementById('category-form').reset();
                showAlert('Categoría creada', 'success');
                return loadCategories();
            })
            .catch(function (err) { showAlert(err.message, 'danger'); });
    });

    document.getElementById('categories-tbody').addEventListener('click', function (event) {
        var btn = event.target.closest('[data-delete-category]');
        if (!btn) { return; }
        if (!confirmAction('¿Eliminar esta categoría? Sus productos quedarán sin categorizar.')) { return; }
        api.delete('/modules/catalog/categories/' + btn.getAttribute('data-delete-category'))
            .then(function () {
                showAlert('Categoría eliminada', 'success');
                return Promise.all([loadCategories(), loadProducts()]);
            })
            .catch(function (err) { showAlert(err.message, 'danger'); });
    });

    var productForm = document.getElementById('product-form');
    var editingId = null;

    function resetProductForm() {
        editingId = null;
        productForm.reset();
        document.getElementById('product-id').value = '';
        document.getElementById('product-submit-btn').textContent = 'Crear producto';
        document.getElementById('product-cancel-btn').style.display = 'none';
        document.getElementById('product-stock-group').style.display = '';
    }

    document.getElementById('product-cancel-btn').addEventListener('click', resetProductForm);

    productForm.addEventListener('submit', function (event) {
        event.preventDefault();

        var payload = {
            sku: document.getElementById('product-sku').value,
            name: document.getElementById('product-name').value,
            description: document.getElementById('product-description').value || null,
            price: document.getElementById('product-price').value,
            category_id: document.getElementById('product-category').value || null,
        };

        var request;
        if (editingId) {
            request = api.put('/modules/catalog/products/' + editingId, payload);
        } else {
            payload.stock_quantity = document.getElementById('product-stock').value || 0;
            request = api.post('/modules/catalog/products', payload);
        }

        request
            .then(function () {
                showAlert(editingId ? 'Producto actualizado' : 'Producto creado', 'success');
                resetProductForm();
                return loadProducts();
            })
            .catch(function (err) { showAlert(err.message, 'danger'); });
    });

    document.getElementById('products-tbody').addEventListener('click', function (event) {
        var editBtn = event.target.closest('[data-edit-product]');
        if (editBtn) {
            var id = editBtn.getAttribute('data-edit-product');
            api.get('/modules/catalog/products/' + id).then(function (p) {
                editingId = p.id;
                document.getElementById('product-id').value = p.id;
                document.getElementById('product-sku').value = p.sku;
                document.getElementById('product-name').value = p.name;
                document.getElementById('product-description').value = p.description || '';
                document.getElementById('product-price').value = p.price;
                document.getElementById('product-category').value = p.category_id || '';
                document.getElementById('product-submit-btn').textContent = 'Guardar cambios';
                document.getElementById('product-cancel-btn').style.display = '';
                document.getElementById('product-stock-group').style.display = 'none';
                productForm.scrollIntoView({ behavior: 'smooth' });
            }).catch(function (err) { showAlert(err.message, 'danger'); });
            return;
        }

        var deleteBtn = event.target.closest('[data-delete-product]');
        if (deleteBtn) {
            if (!confirmAction('¿Eliminar este producto?')) { return; }
            api.delete('/modules/catalog/products/' + deleteBtn.getAttribute('data-delete-product'))
                .then(function () {
                    showAlert('Producto eliminado', 'success');
                    return loadProducts();
                })
                .catch(function (err) { showAlert(err.message, 'danger'); });
            return;
        }

        var adjustBtn = event.target.closest('[data-adjust-product]');
        if (adjustBtn) {
            var productId = adjustBtn.getAttribute('data-adjust-product');
            var delta = parseInt(adjustBtn.getAttribute('data-delta'), 10);
            api.post('/modules/catalog/products/' + productId + '/stock', { delta: delta })
                .then(function () { return loadProducts(); })
                .catch(function (err) { showAlert(err.message, 'danger'); });
        }
    });

    loadCategories().then(loadProducts).catch(function (err) { showAlert(err.message, 'danger'); });
})();
