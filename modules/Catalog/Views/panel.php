<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Catálogo — Misi</title>
    <link rel="stylesheet" href="/css/misi.css">
    <link rel="stylesheet" href="/css/catalog.css">
</head>
<body>

<nav class="catalog-nav">
    <span class="catalog-nav-brand">Catálogo</span>
    <span class="catalog-nav-subtitle">Módulo Catalog (Fase 17)</span>
</nav>

<main class="catalog-main">

    <!-- ============ CATEGORÍAS ============ -->
    <section class="sd-card catalog-section">
        <div class="sd-card-header">
            <h2 class="sd-card-title">Categorías</h2>
        </div>

        <form id="category-form" class="catalog-form-row">
            <div class="sd-form-group">
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
            <div class="catalog-form-grid">
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
                <div class="sd-form-group span-2">
                    <label class="sd-label" for="product-name">Nombre</label>
                    <input class="sd-input" type="text" id="product-name" name="name" required maxlength="150">
                </div>
                <div class="sd-form-group span-2">
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
            <div class="catalog-form-actions">
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
<script src="/js/catalog.js"></script>

</body>
</html>
