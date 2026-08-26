# Instalación de Misi

Misi se instala **una sola vez por máquina**, igual que `pnpm`, `rustup`
o el CLI de Django: después de eso, el comando `misi` está disponible
en cualquier carpeta para crear y administrar proyectos.

No hay un registro de paquetes tipo npm/PyPI detrás de esto — Misi es
una herramienta propia, no pública. Lo que el instalador hace es
clonar tu propio repositorio de Misi (privado o no) a una carpeta fija
del sistema y dejar `misi` apuntando ahí. Ver "Cómo funciona" al final
si quieres el detalle completo.

---

## 1. Instalar

```bash
curl -fsSL https://raw.githubusercontent.com/TU_USUARIO/misi-framework/main/install.sh | sh
```

Reemplaza `TU_USUARIO/misi-framework` por la ruta real de tu
repositorio antes de usar este comando (ver sección 5).

Esto:

1. Clona el framework en `~/.misi/framework`.
2. Enlaza `misi` a una carpeta que ya esté en tu `PATH` (normalmente
   `~/.local/bin`).

Si `~/.local/bin` no estaba en tu `PATH`, el instalador te lo dice al
final con la línea exacta que hay que agregar a tu `~/.bashrc` /
`~/.zshrc`.

Requiere `git` y `php` (>= 8.1) ya instalados en tu sistema — el
instalador los verifica y te avisa si falta alguno.

### Verifica que quedó bien

```bash
misi version
```

```text
Misi Framework 1.2.0
```

---

## 2. Crear un sitio nuevo

Desde **cualquier carpeta** (no hace falta tener nada de Misi cerca):

```bash
misi new tienda-maria
cd tienda-maria
```

Esto crea `tienda-maria/` con toda la estructura del framework: lógica
(`framework/`, `app/`), configuración (`config/`, `.env`), rutas,
vistas, base de datos (`database/migrations/`) — lista para editar.

```text
Creando proyecto Misi en /ruta/tienda-maria...

✓ Estructura del proyecto
✓ Configuración
✓ Environment (.env creado a partir de .env.example)

Proyecto creado exitosamente.
```

---

## 3. Configurar y auditar el proyecto

Edita `.env` con tus credenciales de MySQL:

```env
DB_HOST=127.0.0.1
DB_DATABASE=tienda_maria
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password
```

Y corre el diagnóstico:

```bash
misi doctor
```

```text
Misi Doctor

✓ PHP 8.3.6
✓ Extensión mbstring
✓ Extensión pdo
✓ Extensión pdo_mysql
✓ .env presente
✓ Configuración (config/*.php) carga sin errores
✓ Conexión a base de datos
✓ storage/logs existe y tiene permiso de escritura
✓ storage/cache existe y tiene permiso de escritura
✓ storage/uploads existe y tiene permiso de escritura

Todo en orden.
```

Si algo sale con `✗`, el mensaje te dice exactamente qué corregir antes
de seguir.

---

## 4. Desarrollar

```bash
misi db migrate          # crea las tablas en tu base de datos
misi db seed               # (opcional) datos iniciales / usuario admin
misi serve                  # levanta http://localhost:8000
```

Generar código sobre la marcha:

```bash
misi make controller Customer
misi make model Product
misi make module Inventory
```

Ver el estado del proyecto en cualquier momento:

```bash
misi route:list        # rutas registradas
misi config:list         # configuración pública (sin secretos)
misi info                   # framework, entorno, base de datos
```

Referencia completa de comandos (todos, con ejemplos):

```bash
misi help
```

---

## 5. Desplegar

Misi no tiene build ni paso de compilación: el proyecto que tienes en
tu carpeta **es** el que va al servidor, tal cual.

1. Ajusta `.env` a producción (`APP_ENV=production`, `APP_DEBUG=false`
   y las credenciales reales de MySQL del hosting).
2. Sube la carpeta del proyecto por FTP al hosting (para InfinityFree y
   equivalentes sin SSH, ver `DEPLOYMENT.md` — trae la estructura
   exacta y el `.htaccess` necesario para que quede protegido).
3. Corre las migraciones. Si el hosting tiene SSH: `misi db migrate`
   directamente ahí. Si no (InfinityFree gratuito): el runner por HTTP
   incluido en `deploy/` (ver `DEPLOYMENT.md`, sección de migraciones
   sin SSH).

No hay un paso de "publicar" ni de "compilar assets": adjuntar las
carpetas del proyecto al hosting **es** el despliegue completo.

---

## 6. Mantener Misi actualizado

```bash
misi self-update
```

Actualiza la instalación global (`~/.misi/framework`) a la última
versión de tu repositorio. Esto afecta a los próximos `misi new` que
hagas — **no** modifica proyectos que ya creaste antes (cada proyecto
tiene su propia copia del framework, congelada al momento de crearlo;
ver "Cómo funciona" abajo).

---

## Cómo funciona (por si quieres entender o modificar el instalador)

Misi no se distribuye como paquete de Composer ni tiene un registro
propio de paquetes al estilo npm — es intencional, no una limitación
temporal (ver `docs/cli.md` / `bin/biz` para el razonamiento completo
detrás de mantener el framework simple y sin dependencias). El truco es
más simple de lo que parece:

- `install.sh` clona tu repositorio de Misi en `~/.misi/framework`.
  **Ese checkout ES la plantilla** — no hay un "paquete" empaquetado
  aparte del framework en sí.
- `misi` (el comando global, `bin/misi`) es un script de shell que, al
  ejecutarse, primero busca si estás parada dentro de un proyecto Misi
  ya creado (sube directorios buscando un `bin/biz`). Si lo encuentra,
  usa **ese** — así cada proyecto sigue funcionando con su propia copia
  congelada del framework, sin importar qué tan vieja o nueva sea.
- Si no encuentra ningún proyecto cerca (por ejemplo, estás en una
  carpeta vacía a punto de correr `misi new`), usa la instalación
  global de `~/.misi/framework` en su lugar.
- `misi new` simplemente copia esa plantilla (la global, o la del
  proyecto donde estés parada) al directorio actual + el nombre que le
  diste.

Por eso `misi self-update` solo toca `~/.misi/framework`: afecta al
"molde" para los proyectos que crees de ahora en adelante, pero un
proyecto ya creado sigue siendo, para siempre, una copia independiente
— exactamente como si hubieras hecho `git clone` a mano.

### Variables de entorno del instalador

| Variable | Para qué | Default |
|---|---|---|
| `MISI_REPO` | URL del repositorio a clonar | (debes definirla, ver abajo) |
| `MISI_REF` | Rama o tag a instalar | `main` |
| `MISI_HOME` | Dónde vive la instalación global | `~/.misi/framework` |

### Publicar tu propio instalador

Antes de compartir el `curl \| sh` con nadie (o contigo misma en otra
máquina), reemplaza el placeholder `TU_USUARIO/misi-framework` en:

- `install.sh` (variable `MISI_REPO`)
- `bin/misi` (mensaje de error cuando no hay proyecto ni instalación)
- Esta guía, sección 1

por la URL real de tu repositorio (puede ser privado — solo necesitas
que `git clone` funcione con las credenciales que ya tengas
configuradas en la máquina donde instales).
