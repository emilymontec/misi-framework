# Despliegue en InfinityFree

Guía para desplegar Misi (y cualquier proyecto construido sobre Misi) en
InfinityFree, el hosting gratuito de referencia para probar/entregar
sistemas pequeños sin costo de infraestructura. Todo lo de aquí aplica
igual a **cualquier hosting compartido barato sin SSH** (Fase 15,
"verificación de compatibilidad real en hosting compartido barato") —
InfinityFree es simplemente el caso más restrictivo, así que si Misi
funciona ahí, funciona en casi cualquier otro hosting PHP/MySQL
económico.

## 1. Restricciones reales de InfinityFree (verificadas, 2026)

Esto **no** es la lista de marketing — es lo que realmente limita cómo
se despliega una app PHP+MySQL ahí:

| Restricción | Detalle | Impacto en Misi |
|---|---|---|
| **Sin SSH** | No hay línea de comandos en el servidor bajo ningún plan gratuito. | `bin/biz` (migrate, seed, make:*) no puede ejecutarse en el servidor. Ver sección 4. |
| **Sin acceso remoto a MySQL** | El foro oficial de InfinityFree lo confirma explícitamente: no es posible conectarse a la base de datos desde fuera de la cuenta de hosting, ni siquiera con las credenciales correctas. Solo PHP corriendo dentro de la cuenta (o phpMyAdmin) puede hablarle a la base de datos. | Ni migraciones ni seeds pueden correrse desde tu máquina local apuntando a producción. Tienen que correr como PHP dentro del hosting. |
| **La raíz web (`htdocs/`) no se puede reapuntar** | No existe una opción para cambiar el document root a un subdirectorio (ej. `htdocs/public/`). El foro de soporte lo confirma: no hay ajuste para eso. | El punto de entrada (`index.php`) tiene que vivir literalmente dentro de `htdocs/`. El resto del framework NO. Ver sección 2. |
| **Sin cron por línea de comandos** | Solo existe un "web cron" (el panel llama periódicamente a una URL tuya por HTTP) — no ejecución de scripts vía cron de sistema. | Misi no depende de cron todavía (no hay colas/jobs en el roadmap), así que hoy no bloquea nada. Si un proyecto futuro necesita una tarea periódica, se implementa como una ruta HTTP protegida por token, exactamente igual que el web-runner de la sección 4. |
| **`mail()` deshabilitado** | El envío de correo saliente (función `mail()` de PHP y SMTP saliente directo) está bloqueado en el plan gratuito. | Misi no implementa envío de correo todavía (fuera del roadmap actual). Si se necesita en el futuro, la opción realista en InfinityFree es un proveedor SMTP externo (ej. Brevo/SendGrid) vía `fsockopen`/cURL a su API — no `mail()` nativo. |
| **`disable_functions` agresivo** | Funciones como `exec`, `shell_exec`, `proc_open`, `symlink`, sockets crudos, suelen estar deshabilitadas. | Verificado (sección 3): el framework no usa ninguna de estas en el camino de ejecución que corre en el servidor. `bin/biz` usa `passthru()` solo para `serve` (servidor embebido local), que nunca se ejecuta en producción. |
| **Límite de 30,000 inodes** y **límite de hits/día** | Cuenta cada archivo y cada request servido. | No afecta el código, pero sí la operación: evitar acumular miles de archivos pequeños (ej. logs sin rotar) y ser consciente de que un proyecto con tráfico real puede necesitar upgrade. Ver sección 6. |
| **PHP 8.1+ disponible** | InfinityFree ofrece selector de versión de PHP en el panel, con versiones actuales (8.x). | Sin impacto — cumple el requisito mínimo de Misi (`composer.json`: `php >= 8.1`). Hay que **seleccionar explícitamente** una versión ≥ 8.1 en el panel; el default puede ser distinto. |
| **SSL gratuito (vía Cloudflare)** | Disponible sin costo, activable desde el panel. | Necesario: `config/session.php` marca las cookies como `Secure` en producción, así que la sesión requiere HTTPS para funcionar. Ver `.htaccess` de la sección 2. |

## 2. Estructura de carpetas: qué va en `htdocs/` y qué no

Esta es la decisión que hace posible todo lo demás. Misi **ya está
diseñado para esto desde la Fase 1**: `public/index.php` solo conoce el
resto del framework por ruta relativa (`../bootstrap/app.php`), nunca
por una ruta absoluta ni por configuración de servidor. Eso significa
que no hace falta reapuntar el document root (que en InfinityFree es
imposible, ver tabla anterior) — basta con subir cada carpeta a su
lugar:

```text
Cuenta de hosting InfinityFree (raíz FTP)
│
├── htdocs/                  ← ÚNICA carpeta accesible por HTTP
│   ├── index.php            (contenido de public/index.php)
│   ├── css/                 (contenido de public/css/)
│   ├── js/                  (contenido de public/js/)
│   └── .htaccess            (deploy/infinityfree/htdocs.htaccess)
│
├── framework/                ← fuera de htdocs: nunca accesible por URL
├── app/
├── modules/
├── bootstrap/
├── config/
├── database/
├── resources/
├── routes/
├── storage/
│   ├── logs/
│   ├── cache/
│   └── uploads/              (Storage sirve estos archivos VÍA la app,
│                              nunca por URL directa — igual que en local)
├── .env                       (subido a mano por FTP, nunca por git)
└── vendor/                    (solo si decides usar Composer; opcional)
```

**Por qué esto es más seguro, no solo posible**: `framework/`, `app/`,
`config/`, `database/`, `storage/` (incluyendo `.env`) quedan **fuera**
del webroot. No hace falta ningún `.htaccess` bloqueándolos como
defensa secundaria — Apache literalmente no tiene forma de servirlos,
porque `htdocs/` es la única carpeta que expone. Esto es estrictamente
mejor que la alternativa típica en otros hosts (subir todo dentro de
`htdocs/` y bloquear con `.htaccess`), donde un error de configuración
puede exponer el código fuente o el `.env`.

### Pasos

1. Conéctate por FTP (FileZilla u otro cliente; InfinityFree no ofrece
   SFTP en el plan gratuito, solo FTP — usa una contraseña fuerte y
   considera que el tráfico FTP no va cifrado).
2. Sube el **contenido** de `public/` (no la carpeta `public/` en sí)
   directamente dentro de `htdocs/`.
3. Sube `framework/`, `app/`, `modules/`, `bootstrap/`, `config/`,
   `database/`, `resources/`, `routes/`, `storage/` como hermanas de
   `htdocs/`, en la raíz de la cuenta.
4. Copia `deploy/infinityfree/htdocs.htaccess` dentro de `htdocs/` y
   renómbralo a `.htaccess`.
5. Crea `storage/logs/`, `storage/cache/`, `storage/uploads/` si el
   cliente FTP no sube carpetas vacías (sube sus `.gitkeep`, o crea un
   archivo cualquiera dentro para que la carpeta exista).

## 3. Auditoría de compatibilidad (funciones no soportadas)

Verificado sobre el código actual del framework: ninguna clase de
`framework/`, `app/`, `modules/` ni el ciclo de request/response usa
`exec`, `shell_exec`, `proc_open`, `system`, `popen` ni `passthru`. La
única ocurrencia de `passthru()` en todo el proyecto está en
`bin/biz`, exclusivamente dentro de la implementación del comando
`serve` (servidor embebido de PHP para desarrollo local) — un comando
que nunca se invoca en producción, porque en producción es Apache quien
sirve las requests, no `php -S`. Esto se vuelve a verificar en cada
cierre de fase como parte de la auditoría de `docs/security.md`.

## 4. Migraciones y seeds sin SSH: el web-runner

Como no hay SSH ni acceso remoto a MySQL, `bin/biz migrate` no puede
ejecutarse contra la base de datos de producción desde tu máquina.
`deploy/infinityfree/web-runner.php` resuelve esto: es el mismo código
que usa `bin/biz` (`Migrator`, `Seeder`), pero disparado por una
petición HTTP protegida por token en vez de por línea de comandos.

### Uso

1. Genera un token largo y aleatorio (no un valor memorable):
   ```bash
   php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
   ```
2. Agrégalo a tu `.env` de producción:
   ```env
   DEPLOY_TOKEN=el-valor-generado-arriba
   ```
3. Sube `web-runner.php` dentro de `htdocs/`, **con un nombre propio**
   que solo tú conozcas (ej. `_tareas-9f2a.php` en vez de
   `web-runner.php` — el token es la protección real, el nombre es una
   capa adicional).
4. Visita las URLs (reemplaza dominio, nombre de archivo y token):
   ```text
   https://tu-dominio.com/_tareas-9f2a.php?token=TU_TOKEN&action=status
   https://tu-dominio.com/_tareas-9f2a.php?token=TU_TOKEN&action=migrate
   https://tu-dominio.com/_tareas-9f2a.php?token=TU_TOKEN&action=seed
   https://tu-dominio.com/_tareas-9f2a.php?token=TU_TOKEN&action=rollback
   ```
   La respuesta es texto plano, igual que la salida de `bin/biz` en tu
   terminal local.
5. **Cuando termines: borra el archivo de `htdocs/` por FTP.** No debe
   quedar accesible de forma permanente — cada despliegue nuevo lo
   vuelves a subir, lo usas, lo borras. Alternativamente, si prefieres
   dejarlo instalado entre despliegues, como mínimo rota
   `DEPLOY_TOKEN` después de cada uso.

Sin `token` correcto (o sin `DEPLOY_TOKEN` configurado), el script
responde `404` genérico — no distingue entre "archivo no existe" y
"token incorrecto", para no darle información a quien esté probando.
Cada ejecución exitosa queda registrada en `storage/logs/` (nivel
`info`, con IP y acción) usando el `Logger` normal de Misi.

## 5. `.env` de producción

`.env` **nunca** se sube por git (ya está en `.gitignore` desde la Fase
1) — se sube por FTP, a mano, directamente a la raíz de la cuenta
(fuera de `htdocs/`, ver sección 2). Puntos específicos de InfinityFree:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com

# InfinityFree NO usa "localhost"/"127.0.0.1" para MySQL, aunque la
# base de datos viva en el mismo servidor. Usa el hostname que te da
# el panel (ej. sql309.infinityfree.com) — se encuentra en
# Panel → MySQL Databases → Details.
DB_HOST=sqlXXX.infinityfree.com
DB_PORT=3306
DB_DATABASE=if0_XXXXXXX_nombre
DB_USERNAME=if0_XXXXXXX
DB_PASSWORD=

STORAGE_DRIVER=local
STORAGE_PATH=storage/uploads

SESSION_COOKIE=misi_session
SESSION_SAME_SITE=Lax

LOG_LEVEL=warning

DEPLOY_TOKEN=solo-mientras-el-web-runner-esté-subido
```

`APP_DEBUG=false` es obligatorio en producción: con `true`,
`Application::handleException()` muestra el stack trace completo en la
respuesta HTTP (ver `docs/logging.md`) — aceptable en local, una fuga de
información real en un hosting público.

## 6. PHP, límites y operación

- **Selecciona PHP ≥ 8.1** explícitamente en el panel de InfinityFree
  (Panel → PHP Version). El default de la cuenta puede no coincidir con
  el mínimo de Misi.
- **HTTPS**: activa el SSL gratuito (Panel → SSL, vía Cloudflare) antes
  de probar login — con `SESSION_SECURE` implícito en producción, la
  cookie de sesión no se envía por HTTP simple. El `.htaccess` de la
  sección 2 ya fuerza el redirect a HTTPS.
- **Límites de subida** (`upload_max_filesize`, `post_max_size`): si el
  panel de InfinityFree expone un ajuste de PHP para esto, súbelo al
  valor que necesite tu proyecto (los límites por defecto del hosting
  gratuito suelen ser bajos). Si no hay forma de ajustarlo, es una
  restricción real del plan gratuito — considera reducir el límite de
  tamaño en tu regla `max_size` de `Validation` (Fase 5) para que el
  usuario reciba un 422 claro de Misi en vez de un error genérico del
  servidor cuando excede lo que el hosting permite.
- **Inodes (30,000) e hits/día**: `storage/logs/` crea un archivo por
  día (Fase 9) — a escala de un solo negocio pequeño esto es
  insignificante (365 archivos/año), pero si administras varios
  proyectos en la misma cuenta, revisa y borra logs antiguos
  periódicamente por FTP. No hay limpieza automática todavía (no hay
  cron real disponible) — se evalúa agregar un `action=clean-logs` al
  web-runner solo si esto se vuelve un problema real en producción.
- **`mail()` deshabilitado**: si un proyecto necesita enviar correo
  (ej. recuperación de contraseña), no está resuelto por Misi hoy. La
  vía realista en InfinityFree es la API HTTP de un proveedor externo
  (Brevo, Resend, etc.) vía cURL, no `mail()`. Se evalúa agregar un
  `Misi\Mail` mínimo solo si 2+ proyectos reales lo necesitan (misma
  regla de oro de abstracciones).

## 7. Checklist de verificación post-despliegue

- [ ] `https://tu-dominio.com/` responde (no 404, no 500).
- [ ] Los assets estáticos cargan (`/css/misi.css`, `/js/api.js`) —
      confirma que Apache los sirve directo sin pasar por `index.php`.
- [ ] `https://tu-dominio.com/` sobre HTTP simple redirige a HTTPS.
- [ ] Login funciona y la cookie de sesión persiste entre requests
      (confirma que `Secure` + HTTPS están bien alineados).
- [ ] `GET /storage/{path*}` sirve un archivo subido de prueba con el
      `Content-Type` correcto (confirma que Storage funciona fuera de
      `htdocs/`).
- [ ] Un intento de acceder a `https://tu-dominio.com/.env` o
      `https://tu-dominio.com/../.env` no devuelve el archivo (debería
      ni siquiera ser alcanzable, al vivir fuera de `htdocs/`).
- [ ] El web-runner (`action=status`) confirma que las migraciones
      corrieron y coincide con lo esperado.
- [ ] El web-runner fue **borrado** de `htdocs/` (o `DEPLOY_TOKEN`
      rotado) después de usarlo.
- [ ] `APP_DEBUG=false` en el `.env` de producción.

## 8. Qué significa esto para proyectos hechos con Misi

Cualquier proyecto que siga la misma convención de carpetas de Misi
(`public/` separado del resto, `public/index.php` con rutas relativas)
hereda esta compatibilidad automáticamente — no hay nada que un
proyecto cliente tenga que reconfigurar. Es exactamente el objetivo del
framework (ver principio en la raíz del proyecto): construir una vez la
capacidad de desplegarse en hosting barato, reutilizarla en cada
proyecto sin volver a resolverlo.

Lo único específico de cada proyecto es su propio `.env` de producción
y, si necesita migraciones/seeds propios, subir el web-runner una vez
más (mismo archivo, mismo procedimiento, token distinto).
