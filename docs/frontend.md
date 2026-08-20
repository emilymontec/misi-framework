# Frontend (UI utilities)

Misi no depende de ningún framework frontend (nada de React/Vue/Angular/
Next.js, 🧊 congelado). Lo que trae es una base mínima de CSS + JS vanilla
para no reinventar botones y modales en cada proyecto — nada más.

## Qué incluye

```text
public/
├── css/misi.css      # design tokens + 8 componentes
└── js/
    ├── api.js          # apiFetch(), objeto api.{get,post,put,patch,delete,upload}
    └── ui.js            # showAlert(), confirmAction(), modal(), formSubmit()
resources/
└── views/ui-kit.php      # referencia viva de todo lo anterior (ruta GET /ui-kit)
```

Los assets viven directamente en `public/`, no en `resources/css`/`resources/js`
como sugería el diagrama original del roadmap — es una desviación
deliberada, explicada más abajo.

## Cargarlo en una página

```html
<link rel="stylesheet" href="/css/misi.css">
<!-- ...contenido... -->
<script src="/js/api.js"></script>
<script src="/js/ui.js"></script>
```

`ui.js` depende de `api.js` (usa `apiFetch`/`api` dentro de `formSubmit`)
— cárgalo siempre después.

## `GET /ui-kit`

Página de referencia con los ocho componentes renderizados y funcionando
de verdad (botones que disparan `showAlert()`/`confirmAction()`, un
modal real, un formulario que envía con `formSubmit()` contra
`/api/validate-demo` y muestra los errores de validación tal cual los
devuelve el backend). Es una demo, igual que `/api/ping` lo es de la
Fase 2 — bórrala o reemplázala en tu proyecto.

## CSS: personalización vía variables

Todo el sistema de diseño se ajusta editando `:root` en
`public/css/misi.css` — no hace falta tocar ningún componente:

```css
:root {
  --sd-color-primary: #7c3aed;
  --sd-font-family: "Inter", sans-serif;
  --sd-radius: 12px;
}
```

Todas las clases usan el prefijo `sd-` para no chocar con el CSS propio
del proyecto.

## Componentes disponibles

| Componente | Clases principales |
|---|---|
| Buttons | `.sd-btn`, `.sd-btn-primary`, `.sd-btn-secondary`, `.sd-btn-danger`, `.sd-btn-sm` |
| Forms | `.sd-form-group`, `.sd-label`, `.sd-input`, `.sd-select`, `.sd-textarea`, `.sd-input-error`, `.sd-field-error`, `.sd-help-text` |
| Tables | `.sd-table` |
| Alerts | `.sd-alert`, `.sd-alert-success/danger/warning/info` (o generadas dinámicamente con `showAlert()`) |
| Modals | `.sd-modal-backdrop`, `.sd-modal`, `.sd-modal-header`, `.sd-modal-footer` |
| Navigation | `.sd-nav`, `.sd-nav-brand`, `.sd-nav-link`, `.is-active` |
| Pagination | `.sd-pagination`, `.is-active`, `.is-disabled` |
| Cards | `.sd-card`, `.sd-card-header`, `.sd-card-title`, `.sd-card-body`, `.sd-card-footer` |

Ver `resources/views/ui-kit.php` para el markup exacto de cada uno.

## JavaScript

### `apiFetch(url, options)` y `api.{get,post,put,patch,delete,upload}`

Wrapper sobre `fetch()` nativo (nada de axios) que entiende el formato
estándar de respuesta de Misi (`docs/http.md`):

```js
try {
  const customer = await api.post('/api/customers', { name: 'Ana', email: 'ana@example.com' });
  showAlert('Cliente creado', 'success');
} catch (error) {
  // error.status  -> código HTTP (422, 401, 500...)
  // error.errors  -> objeto de errores de ValidationException, si aplica
  // error.message -> mensaje de la respuesta o "Error {status}"
  showAlert(error.message, 'danger');
}
```

Para peticiones que mutan estado (todo salvo GET/HEAD/OPTIONS),
`apiFetch` obtiene el token CSRF automáticamente desde
`/api/csrf-token` (cacheado en memoria, una sola petición por carga de
página) y lo manda en el header `X-CSRF-Token` — no hay que pensar en
CSRF manualmente desde el frontend. Si tu proyecto expone el token en
otra ruta, ajústalo con `apiFetch.csrfTokenUrl = '/otra/ruta';` antes de
la primera petición mutante.

`api.upload(url, formData)` existe para subidas de archivo (Fase 8): no
fuerza `Content-Type`, el navegador pone el boundary del `multipart/form-data`
correcto automáticamente.

### `showAlert(message, type, options)`

```js
showAlert('Guardado correctamente', 'success');
showAlert('Algo salió mal', 'danger', { duration: 0 }); // 0 = no se autodescarta
```

Inserta una alerta flotante (`.sd-alerts`, esquina superior derecha) con
botón de cierre. `type`: `success | danger | warning | info`.

### `confirmAction(message)`

```js
if (confirmAction('¿Eliminar este cliente?')) {
  await api.delete('/api/customers/42');
}
```

Envuelve el `confirm()` nativo del navegador — simple y funciona en
cualquier lado sin CSS adicional. Si tu proyecto necesita un modal de
confirmación con estilo propio, constrúyelo sobre `modal()` (abajo) y
reemplaza esta función.

### `modal(target, action)`

```js
modal('mi-modal', 'open');
modal('mi-modal', 'close');
modal('mi-modal'); // toggle
```

Requiere el markup de `.sd-modal-backdrop > .sd-modal` (ver
`resources/views/ui-kit.php`). Se cierra automáticamente con `Escape`,
al hacer click fuera del modal, o con cualquier elemento
`data-modal-close="idDelModal"` dentro de él.

### `formSubmit(form, { onSuccess, onError })`

Intercepta el `submit` de un `<form>` y lo manda vía `api` en vez de
navegar (evita el refresh de página completo):

```js
formSubmit(document.querySelector('#customer-form'), {
  onSuccess: (data) => showAlert('Guardado', 'success'),
  onError: (error) => showAlert(error.message, 'danger'),
});
```

Usa el método del `<form method="...">` (o `data-method="PUT"` para
métodos que los forms HTML no soportan nativamente). Si el form tiene un
`<input type="file">`, se manda como `FormData` real (multipart), igual
que un envío tradicional. Si no se pasa `onError`, por defecto muestra
`showAlert(error.message, 'danger')` — no falla en silencio.

## Por qué los assets viven en `public/`, no en `resources/`

El diagrama original del roadmap (sección "Estructura inicial") ponía
`css/`/`js/` bajo `resources/`. En la práctica, sin build step (Misi
evita Node.js en producción a propósito, ver `docs/architecture.md`),
mantener el CSS/JS fuente en `resources/` habría significado servirlos a
través de una ruta de Misi (con el overhead de PHP en cada petición de
asset) o duplicarlos manualmente en `public/` en cada cambio. Ninguna de
las dos opciones es mejor que simplemente escribirlos directamente donde
se sirven: `public/css/` y `public/js/`, servidos por Apache/Nginx (o el
servidor embebido) sin pasar por PHP en absoluto. `resources/views/`
queda para contenido que si necesita PHP (como `ui-kit.php`).

## El servidor embebido de PHP y los archivos estáticos: `bin/server.php`

`php bin/biz serve` no usa `public/index.php` directamente como router
script del servidor embebido — usa `bin/server.php`. La razón es un
problema real que se descubrió al agregar los assets de esta fase:

- Cuando `php -S` recibe un router script, **todas** las peticiones
  pasan por él, incluidas las de archivos estáticos reales.
- `public/index.php` arranca la aplicación incondicionalmente — nunca le
  devuelve el control al servidor embebido para que sirva un archivo
  real. Eso significa que usarlo directamente como router script hace
  que `/css/misi.css` devuelva el 404 JSON de Misi en vez del CSS real
  (así se descubrió el bug: los tests automatizados de esta fase
  fallaron exactamente en eso).
- `bin/server.php` resuelve esto con una sola comprobación: si la URL
  pedida corresponde a un archivo que existe de verdad en `public/`
  (y no es `index.php` mismo), le devuelve el control al servidor
  embebido (`return false;`) para que lo sirva tal cual — igual que
  haría Apache/Nginx con un archivo estático. Cualquier otra URL sigue
  yendo a `public/index.php` normalmente (incluidas las rutas dinámicas
  de Storage, `/storage/{path*}`, que no son archivos reales en
  `public/`).

`public/index.php` se mantiene sin cambios — sigue siendo exactamente lo
que Apache/Nginx invocarían en producción. `bin/server.php` es
exclusivo del entorno de desarrollo con el servidor embebido; nunca se
usa en un hosting real.

## Qué NO hace esta fase (a propósito, 🧊 congelado)

- **Ningún framework frontend** (React/Vue/Angular/Next.js) como
  dependencia. Si un proyecto necesita algo más sofisticado, es una
  decisión de ese proyecto — el framework no lo impone ni lo prepara.
- **Sin sistema de componentes con estado** (nada de "props" o
  reactividad) — son clases CSS + funciones JS puntuales, no un
  framework de UI.
- **Sin motor de plantillas PHP** (tipo Blade/Twig). `ui-kit.php` es PHP
  plano con `include`/output buffering — suficiente por ahora; se agrega
  un motor solo si la repetición de HTML entre vistas de un proyecto
  real lo justifica.
- **Sin bundler/build step** (Webpack, Vite, esbuild). El CSS/JS es
  vanilla, servido tal cual — no hay nada que compilar.
