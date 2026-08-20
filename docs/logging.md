# Logging & Error Handling

`Misi\Logging\Logger` escribe a `storage/logs/misi-YYYY-MM-DD.log` (un
archivo por día). Se accede vía `app()->logger()`, o se usa
automáticamente — **la mayoría de proyectos nunca necesitan llamarlo a
mano**, porque `Application::handleException()` ya lo conecta al manejo
de errores centralizado (Fase 1).

## Qué se registra automáticamente y qué no

| Situación | ¿Se loguea? | Por qué |
|---|---|---|
| `ValidationException` (422) | No | Input inválido del usuario es tráfico normal, no un fallo del sistema. |
| `NotFoundException` (404) | No | Una URL inexistente es tráfico normal. |
| `AuthenticationException` (401), `AuthorizationException` (403), CSRF (419) | No | Parte esperada del flujo de cualquier aplicación con login. |
| Cualquier `HttpException` con status ≥ 500 (ej. `DatabaseException`, `StorageException`) | Sí, nivel `error` | Indica un problema real: la base de datos no respondió, un archivo no se pudo escribir, etc. |
| Excepción no controlada (cualquier otra) | Sí, nivel `critical` | Por definición, algo que el código no anticipó. |

Esto evita el problema típico de "el log tiene 50,000 líneas de 404s y
nadie revisa nada" — solo lo que de verdad amerita atención llega ahí.

## Uso manual (cuando sí lo necesitas)

```php
app()->logger()->info('Pedido creado', ['order_id' => $id]);
app()->logger()->warning('Stock bajo', ['product_id' => $id, 'quantity' => 2]);
```

Niveles disponibles: `debug`, `info`, `warning`, `error`, `critical`.

## Redacción automática de datos sensibles

Cualquier clave del array de contexto que contenga `password`,
`contraseña`, `token`, `secret`, `api_key`, `authorization`, `csrf`,
`credit_card`, `cvv` o `tarjeta` (sin importar mayúsculas/minúsculas) se
reemplaza por `[REDACTED]` antes de escribirse — de forma recursiva, así
que también protege arrays anidados:

```php
app()->logger()->error('Login fallido', ['email' => $email, 'password' => $password]);
// [2026-08-18 19:44:28] ERROR Login fallido {"email":"ana@example.com","password":"[REDACTED]"}
```

**Esto protege el contexto, no el mensaje principal.** Nunca interpoles
un valor sensible directamente en el string del mensaje:

```php
// MAL — el password queda expuesto en texto plano en el log
app()->logger()->error("Login fallido para {$email} con password {$password}");

// BIEN — el valor sensible va en el context, donde sí se redacta
app()->logger()->error('Login fallido', ['email' => $email, 'password' => $password]);
```

## Por qué no se registra el stack trace completo

`Application::handleException()` registra clase, mensaje, archivo y línea
de la excepción — pero deliberadamente **no** llama a
`$e->getTraceAsString()`. En PHP, cada frame de un stack trace puede
incluir los argumentos reales con los que se llamó a cada función (por
ejemplo, la contraseña pasada a `Auth::attempt($email, $password)`
aparecería en texto plano en el trace). La redacción de `Logger` protege
el array de contexto, pero un trace es texto libre que no puede sanearse
de la misma forma — así que se omite por completo. El `file`/`line` de la
excepción y el mensaje suelen ser suficientes para ubicar el problema; si
hace falta más detalle, se reproduce en un entorno con `APP_DEBUG=true`
en vez de confiar en logs de producción.

## Errores de base de datos: mensaje seguro + detalle en el log

`Database::connection()` envuelve cualquier fallo de PDO en
`DatabaseException`, con un mensaje genérico hacia el cliente
("No fue posible conectar a la base de datos.") pero conservando el
`PDOException` original vía `getPrevious()`. `Application` registra ese
detalle completo en el log (`caused_by`) sin que la respuesta HTTP lo
exponga nunca — verificado explícitamente: un fallo de conexión con
credenciales incorrectas nunca deja ver la contraseña real, ni en la
respuesta ni en el log.

## Nivel mínimo configurable

`config/logging.php` lee `LOG_LEVEL` del `.env` (por defecto `debug`).
Cualquier llamada por debajo de ese nivel se descarta silenciosamente —
útil para bajar el ruido en producción sin tocar código:

```env
# .env de producción
LOG_LEVEL=warning
```

## Rotación

Un archivo por día, sin configuración adicional — suficiente para el
tamaño de proyecto que Misi apunta a resolver, y funciona en cualquier
hosting compartido sin `logrotate` ni cron. Si un proyecto real acumula
demasiados logs, un cron simple que borre archivos viejos
(`find storage/logs -mtime +30 -delete`) resuelve esto sin que el
framework necesite implementar rotación por tamaño.

## Qué NO hace Logging (a propósito)

- No hay handlers múltiples (enviar a Slack, Sentry, syslog). Se agrega
  solo si un proyecto real lo necesita.
- No hay formato estructurado (JSON por línea) — texto plano legible es
  suficiente y más fácil de `grep` a mano en un servidor compartido.
- No reemplaza un sistema de monitoreo/alertas. Es un log de texto para
  diagnóstico manual, no una herramienta de observabilidad.
