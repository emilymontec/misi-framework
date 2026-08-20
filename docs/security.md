# Security

Checklist transversal de seguridad para Misi y guía de uso de CSRF. Se
revisa contra todo el código existente al cerrar cada fase (ver
"Auditoría" al final).

## CSRF

`Misi\Security\Csrf` genera un token por sesión (no por request) y lo
valida con `hash_equals()`. El middleware `csrf` (registrado
automáticamente por `Application`) lo aplica solo a métodos que mutan
estado — `GET`/`HEAD`/`OPTIONS` pasan siempre.

### En un formulario HTML tradicional

```php
<form method="POST" action="/customers">
    <?= csrf_field() ?>
    <input type="text" name="name">
</form>
```

### En fetch/AJAX

```js
const token = await fetch('/api/csrf-token').then(r => r.json()).then(d => d.data.token);

await fetch('/api/customers', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': token,
    },
    body: JSON.stringify({ name: 'Ana' }),
});
```

### En una ruta

```php
$router->post('/customers', [CustomerController::class, 'store'], ['auth', 'csrf']);
```

Un token ausente o inválido responde `419` (no `401`/`403` — el problema
es específicamente el token, no la autenticación ni la autorización) y
**no** afecta la sesión activa: un intento de CSRF fallido no cierra la
sesión del usuario legítimo.

### Por qué importa incluso siendo una API JSON

CSRF ataca específicamente peticiones que llevan cookies automáticamente
(el navegador las adjunta aunque la petición venga de otro sitio). Como
`Auth` (Fase 6) usa sesión por cookie, cualquier endpoint que mute datos
mientras el usuario tiene sesión activa es un objetivo válido — no hace
falta que la respuesta sea HTML para que el ataque funcione.

## Checklist de seguridad (auditoría por fase)

| Riesgo | Estado | Dónde se mitiga |
|---|---|---|
| **SQL injection** | ✅ Mitigado | `Database` usa exclusivamente prepared statements (`PDO::ATTR_EMULATE_PREPARES => false`). Ningún método concatena input del usuario en SQL. |
| **XSS** | ✅ Mitigado donde aplica | Sin motor de plantillas todavía (Fase 13), el único output HTML dinámico (`WelcomeController::greet`) escapa con `htmlspecialchars()`. Cuando se agregue un sistema de vistas, el escape por defecto es un requisito no negociable. |
| **CSRF** | ✅ Mitigado | Ver arriba. |
| **Session fixation** | ✅ Mitigado | `Auth::attempt()`/`login()` regeneran el ID de sesión ANTES de guardar el usuario (Fase 6). |
| **Session hijacking** | ✅ Mitigado (dentro de lo posible sin HTTPS propio) | Cookies `HttpOnly` siempre; `Secure` automático en producción (`config/session.php`). El propio HTTPS del hosting es responsabilidad de la infraestructura, no del framework. |
| **Path traversal** | ✅ Mitigado | `LocalStorage::fullPath()` rechaza cualquier ruta con `..` (`StorageException`, 422), tanto al guardar como al servir. Probado explícitamente con `GET /storage/../../../../etc/passwd` → 422, nunca llega al filesystem real. |
| **File upload vulnerabilities** | ✅ Mitigado | `Validation` (Fase 5) verifica MIME real (`image`) y extensión permitida (`mimes`) antes de guardar. `LocalStorage` además: genera un nombre nuevo siempre (nunca el original), guarda fuera del document root público (`storage/uploads/`, no accesible por URL directa), fija permisos `0644` (nunca ejecutable), y vuelve a verificar `is_uploaded_file()` por su cuenta sin confiar ciegamente en Validation. Ver `docs/storage.md`. |
| **Privilege escalation / IDOR** | 🟡 Responsabilidad del proyecto | `Auth::can()` (Fase 6.1) verifica permisos por acción, pero **no** verifica automáticamente propiedad de un recurso (ej. "el pedido 42 es de este usuario"). Cada controlador/service debe comparar explícitamente `business_id`/`owner_id` contra `Auth::id()`. No se automatiza porque depende del modelo de datos de cada proyecto — ver `docs/authorization.md`. |
| **Exposición de información / errores** | ✅ Mitigado | `Application::handleException()` no muestra stack traces cuando `APP_DEBUG=false`; errores no controlados devuelven un mensaje genérico. `Auth::user()` nunca expone el hash de password. Verificado con un fallo real de conexión a MySQL: la contraseña de la base de datos no aparece ni en la respuesta HTTP ni en el log (Fase 9, ver `docs/logging.md`). |
| **Logs con datos sensibles** | ✅ Mitigado | `Logger` redacta recursivamente claves de contexto conocidas (`password`, `token`, `secret`, etc.) y nunca registra el stack trace completo, precisamente porque un trace puede incluir argumentos sensibles de función. Ver `docs/logging.md`. |
| **Secretos expuestos** | ✅ Mitigado | `.env` está en `.gitignore`; solo `.env.example` (sin valores reales) se versiona. Ninguna clase del framework hardcodea credenciales — todo pasa por `Env::get()` dentro de `config/*.php`. |
| **Errores de autorización en frontend** | ✅ Por diseño | Toda decisión de autorización (`Auth::check()`, `Auth::can()`) se evalúa en el backend, en middleware o en el controlador — nunca se asume que el frontend ya filtró algo. |

## Qué NO se implementa todavía (a propósito)

- **Rate limiting / protección de fuerza bruta en login.** No hay
  bloqueo de intentos fallidos todavía. Se evalúa si un proyecto real lo
  necesita (ej. limitar intentos por IP con una tabla simple de
  `login_attempts`), sin agregar dependencias externas.
- **Cabeceras de seguridad HTTP** (`Content-Security-Policy`,
  `X-Frame-Options`, `Strict-Transport-Security`, etc.). Se agregan en la
  Fase 15 (Hardening) como parte de la revisión final, no antes — no
  tiene sentido configurarlas mientras la superficie de la aplicación
  (rutas, vistas) todavía está cambiando fase a fase.
- **2FA.** Fuera de alcance salvo necesidad real explícita de un proyecto.
