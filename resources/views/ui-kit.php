<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Misi — UI Kit</title>
    <link rel="stylesheet" href="/css/misi.css">
</head>
<body>

<nav class="sd-nav">
    <a href="/" class="sd-nav-brand">Misi</a>
    <a href="#buttons" class="sd-nav-link">Buttons</a>
    <a href="#forms" class="sd-nav-link">Forms</a>
    <a href="#table" class="sd-nav-link">Table</a>
    <a href="#alerts" class="sd-nav-link">Alerts</a>
    <a href="#modal" class="sd-nav-link">Modal</a>
    <a href="#cards" class="sd-nav-link">Cards</a>
    <a href="#pagination" class="sd-nav-link">Pagination</a>
</nav>

<main style="max-width: 720px; margin: 32px auto; padding: 0 20px;">

    <h1>Misi UI Kit</h1>
    <p class="sd-text-muted">
        Referencia viva de los componentes de <code>public/css/misi.css</code> +
        <code>public/js/api.js</code> + <code>public/js/ui.js</code>. Esta página
        no es parte del framework — es la demo de la Fase 13, igual que
        <code>/api/ping</code> lo es de la Fase 2. Bórrala o reemplázala
        libremente en tu proyecto.
    </p>

    <section id="buttons" class="sd-card">
        <div class="sd-card-header"><h2 class="sd-card-title">Buttons</h2></div>
        <div class="sd-card-body" style="display:flex; gap:8px; flex-wrap:wrap;">
            <button class="sd-btn sd-btn-primary">Primary</button>
            <button class="sd-btn sd-btn-secondary">Secondary</button>
            <button class="sd-btn sd-btn-danger" id="demo-confirm-btn">Danger (con confirm)</button>
            <button class="sd-btn sd-btn-primary sd-btn-sm">Small</button>
            <button class="sd-btn sd-btn-primary" disabled>Disabled</button>
        </div>
    </section>

    <section id="forms" class="sd-card">
        <div class="sd-card-header"><h2 class="sd-card-title">Forms (con formSubmit + api real)</h2></div>
        <div class="sd-card-body">
            <form id="demo-form" action="/api/validate-demo" method="POST">
                <div class="sd-form-group">
                    <label class="sd-label" for="demo-name">Nombre</label>
                    <input class="sd-input" type="text" id="demo-name" name="name" placeholder="Ana Pérez">
                    <p class="sd-help-text">Prueba enviar vacío para ver el error de validación real.</p>
                </div>
                <div class="sd-form-group">
                    <label class="sd-label" for="demo-email">Email</label>
                    <input class="sd-input" type="email" id="demo-email" name="email" placeholder="ana@example.com">
                </div>
                <button type="submit" class="sd-btn sd-btn-primary">Enviar</button>
            </form>
        </div>
    </section>

    <section id="table" class="sd-card">
        <div class="sd-card-header"><h2 class="sd-card-title">Table</h2></div>
        <table class="sd-table">
            <thead>
                <tr><th>Nombre</th><th>Email</th><th>Rol</th></tr>
            </thead>
            <tbody>
                <tr><td>Ana Pérez</td><td>ana@example.com</td><td>admin</td></tr>
                <tr><td>Juan Gómez</td><td>juan@example.com</td><td>vendedor</td></tr>
            </tbody>
        </table>
    </section>

    <section id="alerts" class="sd-card">
        <div class="sd-card-header"><h2 class="sd-card-title">Alerts (dinámicas, vía showAlert())</h2></div>
        <div class="sd-card-body" style="display:flex; gap:8px; flex-wrap:wrap;">
            <button class="sd-btn sd-btn-secondary" data-alert="success">Success</button>
            <button class="sd-btn sd-btn-secondary" data-alert="danger">Danger</button>
            <button class="sd-btn sd-btn-secondary" data-alert="warning">Warning</button>
            <button class="sd-btn sd-btn-secondary" data-alert="info">Info</button>
        </div>
    </section>

    <section id="modal" class="sd-card">
        <div class="sd-card-header"><h2 class="sd-card-title">Modal</h2></div>
        <div class="sd-card-body">
            <button class="sd-btn sd-btn-primary" onclick="modal('demo-modal', 'open')">Abrir modal</button>
        </div>
    </section>

    <section id="cards" class="sd-card">
        <div class="sd-card-header"><h2 class="sd-card-title">Cards</h2></div>
        <div class="sd-card-body">
            <div class="sd-card" style="box-shadow:none; border-color: var(--sd-color-border);">
                <div class="sd-card-header">
                    <h3 class="sd-card-title">Card anidada</h3>
                </div>
                <div class="sd-card-body sd-text-muted">
                    Así se ve un .sd-card dentro de otro — útil para paneles dentro de un layout.
                </div>
                <div class="sd-card-footer">
                    <button class="sd-btn sd-btn-secondary sd-btn-sm">Acción</button>
                </div>
            </div>
        </div>
    </section>

    <section id="pagination" class="sd-card">
        <div class="sd-card-header"><h2 class="sd-card-title">Pagination</h2></div>
        <ul class="sd-pagination">
            <li><span class="is-disabled">&laquo;</span></li>
            <li><a href="#" class="is-active">1</a></li>
            <li><a href="#">2</a></li>
            <li><a href="#">3</a></li>
            <li><a href="#">&raquo;</a></li>
        </ul>
    </section>

</main>

<div class="sd-modal-backdrop" id="demo-modal">
    <div class="sd-modal">
        <div class="sd-modal-header">
            <h3 class="sd-modal-title">Confirmar acción</h3>
        </div>
        <p class="sd-text-muted">Este es el markup completo de un modal — ábrelo/ciérralo con <code>modal('demo-modal')</code>.</p>
        <div class="sd-modal-footer">
            <button class="sd-btn sd-btn-secondary" data-modal-close="demo-modal">Cancelar</button>
            <button class="sd-btn sd-btn-primary" data-modal-close="demo-modal">Confirmar</button>
        </div>
    </div>
</div>

<script src="/js/api.js"></script>
<script src="/js/ui.js"></script>
<script>
    document.getElementById('demo-confirm-btn').addEventListener('click', function () {
        if (confirmAction('¿Seguro que quieres continuar?')) {
            showAlert('Confirmado.', 'success');
        } else {
            showAlert('Cancelado.', 'info');
        }
    });

    document.querySelectorAll('[data-alert]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            showAlert('Esta es una alerta de tipo "' + btn.dataset.alert + '".', btn.dataset.alert);
        });
    });

    formSubmit(document.getElementById('demo-form'), {
        onSuccess: function (data) {
            showAlert('Datos válidos: ' + JSON.stringify(data), 'success');
        },
        onError: function (error) {
            var detail = error.errors ? JSON.stringify(error.errors) : error.message;
            showAlert('Error de validación: ' + detail, 'danger', { duration: 8000 });
        },
    });
</script>
</body>
</html>
