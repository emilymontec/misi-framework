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
| **Cabeceras de seguridad HTTP** | ✅ Mitigado (Fase 15) | `Application::applyDefaultSecurityHeaders()` aplica `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN` y `Referrer-Policy: strict-origin-when-cross-origin` a TODA respuesta (éxito y error, HTML y JSON) en un único lugar. `Strict-Transport-Security` se agrega automáticamente cuando la conexión es HTTPS — detectado tanto por `$_SERVER['HTTPS']` como por `X-Forwarded-Proto` (necesario detrás de un proxy que termina TLS, como Cloudflare en InfinityFree). Un controlador puede sobrescribir cualquiera de estos con `$response->header(...)` si su caso lo justifica. **Deliberadamente no incluye `Content-Security-Policy`**: no existe un default razonable que no rompa proyectos con `<script>` inline sin nonce (el propio `examples/demo-app` los usa extensivamente) — cada proyecto lo agrega según su propia superficie de scripts si lo necesita. |
| **Inyección de identificadores SQL** (nombres de tabla/columna, no valores) | ✅ Mitigado (Fase 15) | `Database::insert()`/`update()`/`delete()` validan `$table` y las claves de `$data` contra `^[a-zA-Z_][a-zA-Z0-9_]*$` antes de interpolarlas en el SQL (PDO no permite parametrizar identificadores, solo valores). Protege contra el error de construir `$data` a partir de input no filtrado (ej. `$db->insert('customers', $request->all())` sin pasar por `Validator` antes) — un nombre de columna con SQL incrustado se rechaza con `DatabaseException` antes de tocar la base de datos, en vez de ejecutarse. Verificado con un intento real de inyección vía nombre de columna contra MariaDB (Fase 15). |

## Qué NO se implementa todavía (a propósito)

- **Rate limiting / protección de fuerza bruta en login.** No hay
  bloqueo de intentos fallidos todavía. Se evalúa si un proyecto real lo
  necesita (ej. limitar intentos por IP con una tabla simple de
  `login_attempts`), sin agregar dependencias externas.
- **2FA.** Fuera de alcance salvo necesidad real explícita de un proyecto.

## Auditoría Fase 15 (hardening)

Revisión completa del código existente (`framework/`, `app/`,
`modules/`, `database/`, `examples/demo-app/`) contra este checklist,
con hallazgos corregidos y verificados end-to-end contra MariaDB real
(no solo revisión estática):

- **Sin SQL concatenado en ningún punto del proyecto** — búsqueda
  exhaustiva de interpolación de variables en queries, cero coincidencias
  fuera de los identificadores ya cubiertos por la validación nueva
  (arriba).
- **Cabeceras de seguridad HTTP** implementadas (arriba) — cerraba el
  único ítem que este mismo checklist marcaba como pendiente para "la
  Fase 15".
- **Validación de identificadores en `Database`** agregada como defensa
  en profundidad (arriba).
- **Dependencia no declarada de `ext-mbstring`**: `Validator` usa
  `mb_strlen()` para medir longitud de strings con soporte de acentos/UTF-8
  (`docs/validation.md`), pero `composer.json` no la declaraba y no había
  ninguna verificación explícita. Sin la extensión, cualquier request que
  tocara una regla `string`/`max`/`min` fallaba con un `Error` no
  controlado ("Call to undefined function") — reproducido y confirmado
  contra un servidor real sin `mbstring` instalada. Corregido en dos
  frentes: `composer.json` ahora declara `ext-mbstring` (instalaciones
  con Composer fallan temprano y con mensaje claro si falta), y
  `bootstrap/autoload.php` (proyecto raíz y `examples/demo-app`, que
  tiene su propia copia) verifica la extensión al arrancar y corta con un
  mensaje explícito en español antes de llegar a `Application` —
  necesario porque el fallback de autoload manual (sin Composer, el caso
  de hosting compartido sin acceso a internet) no pasa por
  `composer.json` en absoluto. `mbstring` viene habilitada por defecto en
  la inmensa mayoría de hosting compartido, incluido InfinityFree, así
  que esto no agrega una dependencia nueva en la práctica — solo hace
  explícito y a prueba de fallos silenciosos algo de lo que el framework
  ya dependía.
- **Auditoría de funciones de shell** (`exec`/`shell_exec`/`proc_open`/
  `system`/`popen`/`passthru`): reconfirmado que la única ocurrencia en
  todo el proyecto es `passthru()` en `bin/biz serve`, exclusivo de
  desarrollo local (ver `docs/deployment-infinityfree.md`).
- **XSS en `examples/demo-app`**: revisado `resources/views/app.php` y su
  JavaScript — el renderizado de datos de clientes usa un `escapeHtml()`
  propio antes de `innerHTML`, y el de pedidos usa `textContent`
  directamente. Sin hallazgos.
- **Rendimiento**: `Auth::can()` (se ejecuta en cada verificación de
  permiso) filtraba `role_user` por `user_id`, columna que no estaba
  cubierta por ningún índice utilizable (la única clave era la
  `PRIMARY KEY` compuesta `(role_id, user_id)`, inútil para un filtro
  solo por `user_id` por la regla de prefijo izquierdo de InnoDB).
  Corregido con una migración aditiva
  (`004_add_role_user_user_id_index.php`, replicada en
  `examples/demo-app`) — verificado con `SHOW INDEX` contra MariaDB real
  que el índice se crea correctamente. El resto de tablas (`uploads`,
  `customers`, `orders` del demo) se revisaron y no mostraron el mismo
  patrón: sus únicas consultas filtradas por clave ya están cubiertas por
  la `PRIMARY KEY` o por una foreign key (indexada automáticamente por
  InnoDB); el `GROUP BY status` de `Reports\SummaryController` es un
  reporte ocasional sobre una tabla pensada para la escala de un negocio
  pequeño, no una consulta caliente — agregarle un índice ahora sería
  optimización sin necesidad real comprobada (regla de oro de
  abstracciones).
- **Manejo de errores**: revisado `Application::handleException()` y los
  controladores de `app/` y `examples/demo-app/app/` — todos delegan en
  las excepciones propias de Fase 9 (`ValidationException`,
  `AuthenticationException`, `AuthorizationException`,
  `DatabaseException`, `StorageException`), ninguno atrapa y silencia
  excepciones ni expone detalles internos en la respuesta. Sin hallazgos
  nuevos más allá del hallazgo de `mbstring` ya corregido.

**Pendiente de esta auditoría**: revisión de rendimiento más allá de
índices (no se identificaron problemas de N+1 explícitos en el código
actual, pero no se ha probado bajo carga real ni con datasets grandes) y
verificación en una cuenta InfinityFree real (ver
`docs/deployment-infinityfree.md`, checklist final).
