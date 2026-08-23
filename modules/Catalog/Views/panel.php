<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Catálogo — Misi</title>
    <link rel="stylesheet" href="/css/misi.css">
</head>
<body>

<nav class="sd-nav">
    <span class="sd-nav-brand">Catálogo</span>
    <span class="sd-text-muted" style="margin-left:8px; font-size:13px;">Módulo Catalog (Fase 17)</span>
</nav>

<main style="max-width: 1040px; margin: 24px auto; padding: 0 20px;">

    <!-- ============ CATEGORÍAS ============ -->
    <section class="sd-card" style="margin-bottom:24px;">
        <div class="sd-card-header">
            <h2 class="sd-card-title">Categorías</h2>
        </div>

        <form id="category-form" style="display:flex; gap:8px; align-items:flex-end; margin-bottom:16px;">
            <div class="sd-form-group" style="flex:1; margin-bottom:0;">
                <label class="sd-label" for="category-name">Nombre</label>
                <input class="sd-input" type="text" id="category-name" name="name" required maxlength="120">
            </div>
            <button type="submit" class="sd-btn sd-btn-primary">Agregar</button>
        </form>

        <table class="sd-table">
            <thead>
                <tr><th>Nombre</th><th></th></tr>
            </thead>
            <tbody id="categories-tbody"></tbody>
        </table>
    </section>

    <!-- ============ PRODUCTOS ============ -->
    <section class="sd-card">
        <div class="sd-card-header">
            <h2 class="sd-card-title">Productos</h2>
        </div>

        <form id="product-form" style="margin-bottom:20px;">
            <input type="hidden" id="product-id" name="id">
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
                <div class="sd-form-group">
                    <label class="sd-label" for="product-sku">SKU</label>
                    <input class="sd-input" type="text" id="product-sku" name="sku" required maxlength="50">
                </div>
                <div class="sd-form-group">
                    <label class="sd-label" for="product-category">Categoría</label>
                    <select class="sd-input" id="product-category" name="category_id">
                        <option value="">Sin categoría</option>
                    </select>
                </div>
                <div class="sd-form-group" style="grid-column: span 2;">
                    <label class="sd-label" for="product-name">Nombre</label>
                    <input class="sd-input" type="text" id="product-name" name="name" required maxlength="150">
                </div>
                <div class="sd-form-group" style="grid-column: span 2;">
                    <label class="sd-label" for="product-description">Descripción</label>
                    <textarea class="sd-textarea" id="product-description" name="description" maxlength="500"></textarea>
                </div>
                <div class="sd-form-group">
                    <label class="sd-label" for="product-price">Precio</label>
                    <input class="sd-input" type="number" step="0.01" min="0" id="product-price" name="price" required>
                </div>
                <div class="sd-form-group" id="product-stock-group">
                    <label class="sd-label" for="product-stock">Stock inicial</label>
                    <input class="sd-input" type="number" step="1" min="0" id="product-stock" name="stock_quantity" value="0">
                    <p class="sd-help-text">Solo aplica al crear. Para ajustar stock de un producto existente, usá los controles de la tabla.</p>
                </div>
            </div>
            <div style="margin-top:12px; display:flex; gap:8px;">
                <button type="submit" class="sd-btn sd-btn-primary" id="product-submit-btn">Crear producto</button>
                <button type="button" class="sd-btn sd-btn-secondary" id="product-cancel-btn" style="display:none;">Cancelar edición</button>
            </div>
        </form>

        <table class="sd-table">
            <thead>
                <tr>
                    <th>SKU</th><th>Nombre</th><th>Categoría</th><th>Precio</th><th>Stock</th><th></th>
                </tr>
            </thead>
            <tbody id="products-tbody"></tbody>
        </table>
    </section>

</main>

<script src="/js/api.js"></script>
<script src="/js/ui.js"></script>
<script>
(function () {
    'use strict';

    // Igual que examples/demo-app/resources/views/app.php: escapeHtml
    // propio antes de innerHTML para datos de texto; los IDs numéricos
    // que van en atributos no necesitan escape (vienen de la base de
    // datos, nunca de un textarea de usuario).
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

    // ---- Categorías: alta y borrado ----

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

    // ---- Productos: alta, edición, borrado, ajuste de stock ----

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
</script>

</body>
</html>
