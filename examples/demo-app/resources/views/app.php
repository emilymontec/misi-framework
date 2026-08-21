<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bordados Ana — demo Misi</title>
    <link rel="stylesheet" href="/css/misi.css">
</head>
<body>

<!-- ============ PANTALLA DE LOGIN ============ -->
<div id="login-screen" style="min-height:100vh; display:flex; align-items:center; justify-content:center;">
    <div class="sd-card" style="width:360px;">
        <div class="sd-card-header">
            <h1 class="sd-card-title">Bordados Ana</h1>
        </div>
        <p class="sd-text-muted" style="margin-bottom:16px;">
            Demo de Misi (Fase 14). Usuario demo: <code>staff@bordados.test</code> / <code>changeme</code>
            (correr <code>php bin/biz db:seed</code> primero).
        </p>
        <form id="login-form">
            <div class="sd-form-group">
                <label class="sd-label" for="login-email">Email</label>
                <input class="sd-input" type="email" id="login-email" name="email" required>
            </div>
            <div class="sd-form-group">
                <label class="sd-label" for="login-password">Contraseña</label>
                <input class="sd-input" type="password" id="login-password" name="password" required>
            </div>
            <button type="submit" class="sd-btn sd-btn-primary" style="width:100%;">Entrar</button>
        </form>
    </div>
</div>

<!-- ============ PANTALLA PRINCIPAL ============ -->
<div id="main-screen" class="sd-hidden">

    <nav class="sd-nav">
        <span class="sd-nav-brand">Bordados Ana</span>
        <a href="#clientes" class="sd-nav-link">Clientes</a>
        <a href="#pedidos" class="sd-nav-link">Pedidos</a>
        <span style="flex:1;"></span>
        <span class="sd-text-muted" id="current-user" style="margin-right:12px; font-size:13px;"></span>
        <button class="sd-btn sd-btn-secondary sd-btn-sm" id="logout-btn">Salir</button>
    </nav>

    <main style="max-width: 960px; margin: 24px auto; padding: 0 20px;">

        <!-- Resumen (modulo Reports) -->
        <section style="display:flex; gap:16px; margin-bottom:24px; flex-wrap:wrap;">
            <div class="sd-card" style="flex:1; min-width:160px; text-align:center;">
                <div class="sd-text-muted" style="font-size:12px; text-transform:uppercase;">Clientes</div>
                <div style="font-size:28px; font-weight:700;" id="summary-customers">—</div>
            </div>
            <div class="sd-card" style="flex:1; min-width:160px; text-align:center;">
                <div class="sd-text-muted" style="font-size:12px; text-transform:uppercase;">Pedidos</div>
                <div style="font-size:28px; font-weight:700;" id="summary-orders">—</div>
            </div>
            <div class="sd-card" style="flex:2; min-width:240px;">
                <div class="sd-text-muted" style="font-size:12px; text-transform:uppercase; margin-bottom:6px;">Por estado</div>
                <div id="summary-by-status" style="font-size:13px;">—</div>
            </div>
        </section>

        <!-- Clientes -->
        <section id="clientes" class="sd-card">
            <div class="sd-card-header">
                <h2 class="sd-card-title">Clientes</h2>
                <button class="sd-btn sd-btn-primary sd-btn-sm" onclick="modal('customer-modal', 'open')">+ Nuevo cliente</button>
            </div>
            <table class="sd-table">
                <thead><tr><th>Nombre</th><th>Email</th><th>Teléfono</th><th></th></tr></thead>
                <tbody id="customers-tbody">
                    <tr><td colspan="4" class="sd-text-muted">Cargando…</td></tr>
                </tbody>
            </table>
        </section>

        <!-- Pedidos -->
        <section id="pedidos" class="sd-card">
            <div class="sd-card-header">
                <h2 class="sd-card-title">Pedidos</h2>
                <button class="sd-btn sd-btn-primary sd-btn-sm" onclick="modal('order-modal', 'open')">+ Nuevo pedido</button>
            </div>
            <table class="sd-table">
                <thead><tr><th>Cliente</th><th>Descripción</th><th>Estado</th><th>Referencia</th><th></th></tr></thead>
                <tbody id="orders-tbody">
                    <tr><td colspan="5" class="sd-text-muted">Cargando…</td></tr>
                </tbody>
            </table>
        </section>

    </main>
</div>

<!-- ============ MODAL: NUEVO CLIENTE ============ -->
<div class="sd-modal-backdrop" id="customer-modal">
    <div class="sd-modal">
        <div class="sd-modal-header">
            <h3 class="sd-modal-title">Nuevo cliente</h3>
        </div>
        <form id="customer-form" action="/api/customers" method="POST">
            <div class="sd-form-group">
                <label class="sd-label" for="customer-name">Nombre</label>
                <input class="sd-input" type="text" id="customer-name" name="name" required>
            </div>
            <div class="sd-form-group">
                <label class="sd-label" for="customer-email">Email</label>
                <input class="sd-input" type="email" id="customer-email" name="email" required>
            </div>
            <div class="sd-form-group">
                <label class="sd-label" for="customer-phone">Teléfono (opcional)</label>
                <input class="sd-input" type="text" id="customer-phone" name="phone">
            </div>
            <div class="sd-modal-footer">
                <button type="button" class="sd-btn sd-btn-secondary" data-modal-close="customer-modal">Cancelar</button>
                <button type="submit" class="sd-btn sd-btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- ============ MODAL: NUEVO PEDIDO ============ -->
<div class="sd-modal-backdrop" id="order-modal">
    <div class="sd-modal">
        <div class="sd-modal-header">
            <h3 class="sd-modal-title">Nuevo pedido</h3>
        </div>
        <form id="order-form" action="/api/orders" method="POST">
            <div class="sd-form-group">
                <label class="sd-label" for="order-customer">Cliente</label>
                <select class="sd-select" id="order-customer" name="customer_id" required>
                    <option value="">Selecciona un cliente…</option>
                </select>
            </div>
            <div class="sd-form-group">
                <label class="sd-label" for="order-description">Descripción</label>
                <input class="sd-input" type="text" id="order-description" name="description" placeholder="Logo bordado en 3 gorras" required>
            </div>
            <div class="sd-form-group">
                <label class="sd-label" for="order-image">Imagen de referencia (opcional)</label>
                <input type="file" id="order-image" name="reference_image" accept="image/png,image/jpeg">
                <p class="sd-help-text">JPG o PNG, máximo 2MB — se valida el contenido real, no solo la extensión.</p>
            </div>
            <div class="sd-modal-footer">
                <button type="button" class="sd-btn sd-btn-secondary" data-modal-close="order-modal">Cancelar</button>
                <button type="submit" class="sd-btn sd-btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script src="/js/api.js"></script>
<script src="/js/ui.js"></script>
<script>
(function () {
    var STATUSES = ['pendiente', 'en_proceso', 'listo', 'entregado'];
    var canManageOrders = false;

    function showLogin() {
        document.getElementById('login-screen').classList.remove('sd-hidden');
        document.getElementById('main-screen').classList.add('sd-hidden');
    }

    function showMain(user) {
        document.getElementById('login-screen').classList.add('sd-hidden');
        document.getElementById('main-screen').classList.remove('sd-hidden');
        document.getElementById('current-user').textContent = user.name + ' (' + user.email + ')';
        canManageOrders = !!user.can_manage_orders;
        loadEverything();
    }

    function loadEverything() {
        loadSummary();
        loadCustomers();
        loadOrders();
    }

    function loadSummary() {
        api.get('/modules/reports/summary').then(function (data) {
            document.getElementById('summary-customers').textContent = data.total_customers;
            document.getElementById('summary-orders').textContent = data.total_orders;
            var byStatus = data.orders_by_status.map(function (row) {
                return row.status + ': ' + row.total;
            }).join(' · ');
            document.getElementById('summary-by-status').textContent = byStatus || 'Sin pedidos todavía.';
        }).catch(function (err) { showAlert(err.message, 'danger'); });
    }

    function loadCustomers() {
        api.get('/api/customers').then(function (customers) {
            var tbody = document.getElementById('customers-tbody');
            var select = document.getElementById('order-customer');

            tbody.innerHTML = customers.length ? '' : '<tr><td colspan="4" class="sd-text-muted">Sin clientes todavía.</td></tr>';
            select.innerHTML = '<option value="">Selecciona un cliente…</option>';

            customers.forEach(function (c) {
                var row = document.createElement('tr');
                row.innerHTML =
                    '<td>' + escapeHtml(c.name) + '</td>' +
                    '<td>' + escapeHtml(c.email) + '</td>' +
                    '<td>' + escapeHtml(c.phone || '—') + '</td>' +
                    '<td><button class="sd-btn sd-btn-danger sd-btn-sm" data-delete-customer="' + c.id + '">Eliminar</button></td>';
                tbody.appendChild(row);

                var opt = document.createElement('option');
                opt.value = c.id;
                opt.textContent = c.name;
                select.appendChild(opt);
            });

            tbody.querySelectorAll('[data-delete-customer]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    if (!confirmAction('¿Eliminar este cliente? También se eliminan sus pedidos.')) return;
                    api.delete('/api/customers/' + btn.dataset.deleteCustomer)
                        .then(function () { showAlert('Cliente eliminado', 'success'); loadEverything(); })
                        .catch(function (err) { showAlert(err.message, 'danger'); });
                });
            });
        }).catch(function (err) { showAlert(err.message, 'danger'); });
    }

    function loadOrders() {
        api.get('/api/orders').then(function (orders) {
            var tbody = document.getElementById('orders-tbody');
            tbody.innerHTML = orders.length ? '' : '<tr><td colspan="5" class="sd-text-muted">Sin pedidos todavía.</td></tr>';

            orders.forEach(function (o) {
                var row = document.createElement('tr');

                var statusSelect = document.createElement('select');
                statusSelect.className = 'sd-select';
                statusSelect.style.width = 'auto';
                STATUSES.forEach(function (s) {
                    var opt = document.createElement('option');
                    opt.value = s;
                    opt.textContent = s;
                    if (s === o.status) opt.selected = true;
                    statusSelect.appendChild(opt);
                });
                statusSelect.addEventListener('change', function () {
                    api.put('/api/orders/' + o.id, { description: o.description, status: statusSelect.value })
                        .then(function () { showAlert('Estado actualizado', 'success'); loadSummary(); })
                        .catch(function (err) { showAlert(err.message, 'danger'); });
                });

                var refCell = document.createElement('td');
                if (o.reference_image_url) {
                    var link = document.createElement('a');
                    link.href = o.reference_image_url;
                    link.target = '_blank';
                    link.textContent = 'Ver imagen';
                    refCell.appendChild(link);
                } else {
                    refCell.className = 'sd-text-muted';
                    refCell.textContent = 'Sin imagen';
                }

                var actionsCell = document.createElement('td');
                if (canManageOrders) {
                    var delBtn = document.createElement('button');
                    delBtn.className = 'sd-btn sd-btn-danger sd-btn-sm';
                    delBtn.textContent = 'Eliminar';
                    delBtn.addEventListener('click', function () {
                        if (!confirmAction('¿Eliminar este pedido?')) return;
                        api.delete('/api/orders/' + o.id)
                            .then(function () { showAlert('Pedido eliminado', 'success'); loadEverything(); })
                            .catch(function (err) { showAlert(err.message, 'danger'); });
                    });
                    actionsCell.appendChild(delBtn);
                } else {
                    actionsCell.className = 'sd-text-muted';
                    actionsCell.textContent = 'Sin permiso';
                }

                var nameTd = document.createElement('td');
                nameTd.textContent = o.customer_name;
                var descTd = document.createElement('td');
                descTd.textContent = o.description;
                var statusTd = document.createElement('td');
                statusTd.appendChild(statusSelect);

                row.appendChild(nameTd);
                row.appendChild(descTd);
                row.appendChild(statusTd);
                row.appendChild(refCell);
                row.appendChild(actionsCell);
                tbody.appendChild(row);
            });
        }).catch(function (err) { showAlert(err.message, 'danger'); });
    }

    function escapeHtml(value) {
        var div = document.createElement('div');
        div.textContent = value;
        return div.innerHTML;
    }

    // --- Login ---
    formSubmit(document.getElementById('login-form'), {
        onSuccess: function (user) { showAlert('Bienvenida, ' + user.name, 'success'); showMain(user); },
        onError: function (err) { showAlert(err.message || 'Credenciales inválidas.', 'danger'); },
    });

    document.getElementById('logout-btn').addEventListener('click', function () {
        api.post('/api/logout', {}).then(function () {
            showAlert('Sesión cerrada', 'info');
            showLogin();
        }).catch(function (err) { showAlert(err.message, 'danger'); });
    });

    // --- Clientes ---
    formSubmit(document.getElementById('customer-form'), {
        onSuccess: function () {
            showAlert('Cliente creado', 'success');
            modal('customer-modal', 'close');
            document.getElementById('customer-form').reset();
            loadEverything();
        },
        onError: function (err) {
            var detail = err.errors ? Object.values(err.errors).flat().join(' ') : err.message;
            showAlert(detail, 'danger', { duration: 8000 });
        },
    });

    // --- Pedidos ---
    formSubmit(document.getElementById('order-form'), {
        onSuccess: function () {
            showAlert('Pedido creado', 'success');
            modal('order-modal', 'close');
            document.getElementById('order-form').reset();
            loadEverything();
        },
        onError: function (err) {
            var detail = err.errors ? Object.values(err.errors).flat().join(' ') : err.message;
            showAlert(detail, 'danger', { duration: 8000 });
        },
    });

    // --- Estado inicial: ¿ya hay sesión? ---
    api.get('/api/me').then(showMain).catch(showLogin);
})();
</script>
</body>
</html>
