# Guía de despliegue de Misi

Cómo llevar Misi a producción: desde preparar el `.env` hasta correr
migraciones en hosting sin SSH. Incluye los dos escenarios reales para
los que está pensada la base: **InfinityFree gratuito** y **hosting
compartido genérico (cPanel/Apache)**.

---

## 1. Requisitos

| Requisito | Detalle |
|---|---|
| PHP | **8.1 o superior** (el código usa `readonly`, `match`, `str_starts_with`) |
| Extensiones PHP | `mbstring` (obligatoria — `bootstrap/autoload.php` lo verifica y falla con mensaje claro), `pdo_mysql`, `fileinfo` |
| Base de datos | MySQL / MariaDB — solo necesaria si usas `Database`; el bootstrap y el router funcionan sin ella |
| Composer | **No requerido**. Si algún día agregas dependencias: `composer install` y sube también `vendor/` (no hay SSH para ejecutarlo en el hosting) |

Verificación local antes de subir:

```bash
php bin/biz serve        # levanta sin errores
curl http://localhost:8000/api/ping   # responde JSON ok:true
```

## 2. Prepara el proyecto antes de subir

Crea tu `.env` a partir de `.env.example` con los valores de producción.
Las variables que maneja Misi:

| Variable | Producción | Notas |
|---|---|---|
| `APP_ENV` | `production` | Fuerza cookies de sesión `Secure` automáticamente |
| `APP_DEBUG` | `false` | `true` SOLO mientras depuras: expone mensajes y traces de excepciones |
| `APP_URL` | `https://tudominio...` | URL pública final |
| `APP_TIMEZONE` | ej. `America/Bogota` | |
| `DB_HOST` | ver escenario A/B | En InfinityFree NUNCA `127.0.0.1`: usa el host exacto del panel |
| `DB_PORT` | `3306` | |
| `DB_DATABASE` / `DB_USERNAME` / `DB_PASSWORD` | del panel | En InfinityFree llevan prefijo obligatorio `if0_TUCUENTA_` |
| `STORAGE_DRIVER` / `STORAGE_PATH` | `local` / `storage/uploads` | Defaults correctos casi siempre |
| `SESSION_COOKIE` / `SESSION_LIFETIME` / `SESSION_SAME_SITE` | defaults OK | `SESSION_SECURE` se fuerza solo por `APP_ENV` |
| `LOG_LEVEL` | `warning` o `error` | `debug` llena el disco con ruido |
| `DEPLOY_TOKEN` | 64 hex aleatorios | Solo necesario para los scripts temporales de despliegue. Genera: `php -r "echo bin2hex(random_bytes(32));"` |

No olvides que `.env` empieza con punto: varios FTP/administradores de
archivos lo omiten o lo esconden al subir. Verifica que quedó.

## 3. Escenario A — InfinityFree (plan gratuito)

### Restricciones reales del plan gratuito (condicionan todo)

1. **La cuenta está enjaulada dentro de `htdocs/`**: no se pueden crear
   carpetas fuera de ese directorio, ni por FTP ni por el administrador
   web ("Can't create directory: Read-only file system"). Por eso TODO
   el proyecto vive dentro de `htdocs/` y el `.htaccess` de la raíz del
   repo hace el trabajo de aislar `framework/`, `config/`, `.env`, etc.
2. **Sin SSH**: `php bin/biz migrate` nunca puede ejecutarse en el
   servidor; las migraciones corren vía HTTP con el deploy-runner
   (sección 5).
3. **MySQL sin acceso remoto**: solo el PHP dentro de la cuenta puede
   conectarse a la base. Desde tu máquina es imposible.
4. **Versión de PHP elegible desde el panel** ("Select PHP Version"):
   debe ser >= 8.1.
5. **Funciones deshabilitadas** (`putenv`, `exec`, `system`,
   `proc_open`, ...): Misi no depende de ninguna; `Env::load()` ya
   protege la llamada a `putenv()`.

### Estructura final en el hosting

```text
htdocs/                          <- raíz web de la cuenta
├── .htaccess                    <- copia EXACTA del .htaccess raíz del repo
├── index.html                   <- landing estática (quítala si no la quieres)
├── app/  bin/  bootstrap/  business/  config/  database/
├── framework/  modules/  resources/  routes/
├── storage/logs|cache|uploads/
├── .env
└── public/
    ├── index.php                <- front controller real
    ├── css/ , js/
    └── <runner>.php             <- (temporal) migraciones vía web
```

Flujo de peticiones: Apache lee `htdocs/.htaccess`, sirve directo lo
que exista físicamente bajo `public/` (css/js) y manda todo lo demás a
`public/index.php`. Cualquier intento de acceder a `framework/`,
`config/` o `.env` por URL termina en el front controller (404 JSON) o
bloqueado por `FilesMatch`.

### Paso a paso

1. **Sube el proyecto DIRECTAMENTE en `htdocs/`** (no en
   `htdocs/misi-framework/`). Con FTP activa "mostrar archivos
   ocultos"; con el administrador web verifica después que existan
   `htdocs/.htaccess` y `htdocs/.env`. Si falta el `.htaccess`, créalo
   vacío ahí mismo, edítalo y pega el contenido del `.htaccess` de la
   raíz del repo. **Es el archivo más importante del despliegue**: sin
   él todas las rutas dan 404 y `/` mostraría la landing `index.html`.
2. **Panel → "Select PHP Version" → 8.1 o superior** (idealmente la
   mayor disponible). Tras cambiarla espera ~1 minuto.
3. **Sube tu `.env` de producción** (sección 2). El host/nombre/usuario
   de base de datos salen del paso 4.
4. **Panel → "MySQL Databases"** → crea la base. Anota host
   (`sqlXXX.infinityfree.com`), nombre (`if0_12345678_misi`), usuario
   (`if0_12345678`) y contraseña MySQL.
5. **Corre migraciones y seeds por HTTP** (sección 5).
6. **Comprueba**: `/` muestra "Misi está funcionando 🎉" y
   `/api/ping` responde JSON. Después apaga `APP_DEBUG`.

## 4. Escenario B — Hosting compartido genérico (cPanel/Apache)

Aplica cuando el hosting SÍ permite carpetas fuera del webroot
(`public_html/`), que es el diseño natural de Misi:

```text
~/mi-proyecto/                   <- fuera del webroot (inaccesible por URL)
├── app/  bootstrap/  config/  framework/  modules/  routes/  ...
├── storage/
└── .env

public_html/                     <- webroot
├── .htaccess                    <- el de public/ (ya tiene el punto)
├── index.php
└── css/ , js/
```

Pasos:

1. Sube todo el proyecto a una carpeta FUERA del webroot (ej.
   `~/mi-proyecto/`).
2. Copia el **contenido** de `public/` dentro de `public_html/`
   (`index.php`, `.htaccess`, `css/`, `js/`). El `index.php` no cambia:
   resuelve el proyecto como `dirname(__DIR__)`.
3. `.env` con `DB_HOST=localhost` (en cPanel lo habitual) y el usuario
   de la base con su prefijo (`cpaneluser_misi`).
4. Migraciones: con SSH → `php bin/biz migrate`; sin SSH → deploy-runner
   (sección 5), dejándolo temporalmente en `public_html/`.

> Si el hosting te obliga a meter todo dentro de `public_html/`, usa la
> variante InfinityFree: `.htaccess` de la raíz del repo + proyecto
> completo dentro del webroot. Funciona idéntico.

## 5. Migraciones y seeds sin SSH (deploy-runner)

Plantilla: `public/__deploy-runner.php.example`. Es `bin/biz migrate`
convertido en endpoint HTTP temporal (reutiliza `Migrator` y `Seeder`),
pensado para InfinityFree donde el único PHP que puede hablar con la
base corre dentro de la cuenta.

1. Define `DEPLOY_TOKEN` en el `.env` del hosting (64 hex aleatorios).
2. Renombra la plantilla quitándole `.example` a un nombre que solo tú
   conozcas (ej. `mig-e7f3.php`) y déjala junto a `index.php` —
   en InfinityFree eso es `htdocs/public/`.
3. Visita (acciones disponibles: `status`, `migrate`, `rollback`,
   `seed`):
   ```
   https://tudominio/mig-e7f3.php?token=TU_TOKEN&action=status
   https://tudominio/mig-e7f3.php?token=TU_TOKEN&action=migrate
   https://tudominio/mig-e7f3.php?token=TU_TOKEN&action=seed
   ```
4. **Borra el archivo del hosting al terminar** (o al menos vacía
   `DEPLOY_TOKEN` en `.env`). No debe quedar permanente en producción.

Seguridad integrada: sin token configurado no hace nada; comparación en
tiempo constante (`hash_equals`); token inválido responde 404 genérico;
cada ejecución queda en `storage/logs/`.

Para diagnóstico de hosting (versión de PHP, mbstring, presencia de
dotfiles, permisos de `storage/`) existe el equivalente
`public/__host-check.php.example` — mismas reglas de uso y borrado.

## 6. Actualizar una versión ya desplegada

| Cambio | Qué subir |
|---|---|
| Código de `app/`, `routes/`, vistas | Los archivos tocados (FTP sobrescribe) |
| `framework/` actualizado | La carpeta entera |
| Migraciones nuevas | Archivos nuevos + correr `action=migrate` con el runner |
| `.env` con variables nuevas | Editarlo en el hosting (no subas tu `.env` local encima sin revisar: las credenciales son distintas) |
| Assets (`css/js`) | Los archivos tocados |

Nunca actives `APP_DEBUG=true` en producción "un rato" y lo olvides:
es la variable que convierte un error interno en información para un
atacante. Para depurar en caliente, súbelo, reproduce, y baja el log
que queda en `storage/logs/misi-YYYY-MM-DD.log`.

## 7. Checklist post-despliegue

- [ ] `https://dominio/` carga la app (no la landing ni error)
- [ ] `https://dominio/api/ping` devuelve JSON `ok`
- [ ] Sirve HTTPS y redirige desde HTTP (lo hace el `.htaccess`)
- [ ] `APP_DEBUG=false`, `APP_ENV=production`, `LOG_LEVEL=warning|error`
- [ ] Deploy-runner borrado del hosting (o `DEPLOY_TOKEN` vacío)
- [ ] Host-check borrado si llegaste a subirlo
- [ ] Login/logout funciona (sesión + cookie Secure + CSRF)
- [ ] Una subida de archivo funciona y aparece en `/storage/...`
- [ ] `storage/logs/misi-*.log` se está escribiendo

## 8. Troubleshooting

### 404 en TODAS las rutas (y `/` muestra la landing `index.html`)
El `.htaccess` de la raíz del webroot no existe o no se ejecutó:
1. Con "show hidden files", confirma que existe y contiene el rewrite a
   `public/index.php`.
2. Confirma que el proyecto está directo en el webroot y no en un
   subdirectorio.
3. Confirma que `public/index.php` existe.

### Parse error "unexpected readonly (T_READONLY)" / pantalla blanca
PHP < 8.1. Cambia la versión en el panel, espera ~1 minuto, recarga.

### "Misi requiere la extensión PHP 'mbstring'"
Actívala en el selector de extensiones del panel (viene habilitada por
defecto en InfinityFree).

### Warnings "putenv() has been disabled"
Ya resuelto en `framework/Support/Env.php` (se invoca solo si existe).
Actualiza ese archivo si tu copia en el hosting es anterior.

### Error de conexión MySQL (`SQLSTATE[HY000] [2002]/[1045]`)
- InfinityFree: host exacto `sqlXXX.infinityfree.com` (nunca
  `localhost`/`127.0.0.1`), usuario/base con prefijo `if0_TUCUENTA_`,
  contraseña MySQL (no la del panel).
- cPanel: normalmente `localhost` y usuario/base `cpaneluser_nombre`.
- Recuerda: contra la base de InfinityFree NO puedes conectarte desde
  tu máquina local, solo desde PHP dentro de la cuenta.

### "No fue posible crear el directorio de logs"
Falta alguna de `storage/logs|cache|uploads` (si subiste con un método
que omitió carpetas vacías, créalas en el hosting). Deben poder
escribirse.

### Página de "Account Suspended" de InfinityFree
Suele ser inactividad o abuso detectado por su sistema automático — no
es un problema de Misi. Se reactiva desde el panel del cliente.
