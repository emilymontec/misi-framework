# Storage

`Misi\Storage\StorageInterface`, implementada por `LocalStorage`, maneja
archivos subidos de forma segura. Se accede vía `app()->storage()`.

## Guardar un archivo subido

```php
use Misi\Auth\Auth;

$data = app()->validator()->validate([
    'file' => $request->file('avatar'),
], [
    'file' => ['required', 'image', 'mimes:jpg,jpeg,png,gif', 'max_size:2048'],
]);

$storage = app()->storage();
$path = $storage->putUploadedFile($data['file'], 'avatars'); // "avatars/3f9a2b1c....png"

app()->database()->insert('uploads', [
    'path' => $path,
    'original_name' => $data['file']['name'],
    'mime_type' => $storage->mimeType($path),
    'size' => $storage->size($path),
    'uploaded_by' => Auth::id(),
    'created_at' => date('Y-m-d H:i:s'),
]);
```

**Siempre valida antes de guardar** (`Validation`, Fase 5) — `Storage` no
sustituye eso, lo complementa. `image`/`mimes` verifican contenido real y
extensión respectivamente; `Storage` además desconfía por su cuenta:
vuelve a comprobar `is_uploaded_file()` y genera un nombre nuevo sin
importar lo que haya validado antes (defensa en profundidad).

## La base de datos solo guarda metadata

`uploads.path` guarda la ruta relativa (`avatars/xxxx.png`), nunca el
archivo en sí. El archivo real vive en `storage/uploads/` (fuera del
document root público — ver siguiente sección).

## Servir el archivo

```php
$router->get('/storage/{path*}', [UploadController::class, 'show']);
```

```php
public function show(Request $request, string $path): Response
{
    $storage = app()->storage();

    if (!$storage->exists($path)) {
        throw new NotFoundException("Archivo no encontrado: {$path}");
    }

    return (new Response($storage->get($path)))
        ->header('Content-Type', $storage->mimeType($path));
}
```

`{path*}` es un parámetro de ruta "catch-all" (ver `docs/routing.md`):
matchea `/`, necesario porque las rutas de storage incluyen
subdirectorios (`avatars/xxxx.png`).

### Archivos privados

Esta demo sirve los archivos sin restricción (piénsalo como un avatar
público). Si tu proyecto guarda documentos que no cualquiera debería
poder ver, agrega el middleware `auth` — o tu propia verificación de
`Auth::can()`/propiedad del recurso — a la ruta:

```php
$router->get('/storage/{path*}', [DocumentController::class, 'show'], ['auth']);
```

```php
public function show(Request $request, string $path): Response
{
    $upload = app()->database()->selectOne('SELECT * FROM uploads WHERE path = ?', [$path]);

    if ($upload === null || (int) $upload['uploaded_by'] !== Auth::id()) {
        throw new NotFoundException('Archivo no encontrado.'); // 404, no 403 — no reveles que existe
    }

    // ... servir igual que arriba
}
```

### Nota sobre el servidor embebido de PHP en desarrollo

`php -S localhost:8000 -t public` **sin** un router script de por medio
intenta servir cualquier URL que termine en una extensión conocida
(`.png`, `.jpg`, `.css`...) como archivo estático directo, sin pasar por
`index.php` — y como los archivos servidos dinámicamente por Storage
(como este) no existen físicamente en `public/`, responde con su propio
404 antes de que Misi intervenga. `php bin/biz serve` (Fase 11) ya
resuelve esto usando `bin/server.php` como router script — ver
`docs/frontend.md` para el detalle completo de por qué existe ese
archivo separado (spoiler: pasar `public/index.php` directamente como
router script resuelve este caso, pero rompe el de servir CSS/JS reales
de la Fase 13, porque `index.php` nunca le devuelve el control al
servidor para archivos que sí existen).

Esto **no** aplica a Apache/Nginx en producción: las reglas de rewrite ya
mandan toda petición a `index.php` sin importar la extensión.

## Eliminar un archivo

```php
$upload = app()->database()->selectOne('SELECT * FROM uploads WHERE id = ?', [$id]);
app()->storage()->delete($upload['path']);
app()->database()->delete('uploads', 'id = ?', [$id]);
```

`delete()` no lanza excepción si el archivo ya no existe (retorna
`false` silenciosamente) — evita que borrar dos veces la misma fila
rompa el flujo.

## Protecciones aplicadas siempre por `LocalStorage`

- **Nombre de archivo generado** (`bin2hex(random_bytes(16))` + extensión
  saneada) — nunca se usa el nombre original del archivo subido.
- **Bloqueo de path traversal**: cualquier ruta con `..` se rechaza con
  `StorageException` (422), tanto al guardar como al leer/servir. Probado
  explícitamente con `GET /storage/../../../../etc/passwd` → 422, nunca
  llega al filesystem real.
- **Permisos 0644** en el archivo guardado — nunca ejecutable.
- **Verificación de `is_uploaded_file()`** antes de mover cualquier
  archivo, incluso si `Validation` ya lo comprobó — no se confía
  ciegamente en una capa anterior.
- **Fuera del document root** por defecto (`storage/uploads/`, no
  `public/uploads/`): aunque alguien lograra subir un archivo malicioso,
  el servidor web no puede ejecutarlo directamente por URL — solo se
  accede a través de la ruta controlada por Misi (`show()` arriba),
  que nunca usa `include`/`require` sobre el contenido, solo lo devuelve
  como bytes con su `Content-Type` real.

## Qué NO hace Storage (a propósito, 🧊 congelado)

- **`S3Storage`/`CloudStorage`**: la interfaz ya está preparada
  (`StorageInterface`), pero no se implementan hasta que un proyecto real
  lo necesite (ej. necesidad real de CDN o de no depender del disco del
  hosting).
- **Redimensionado/procesamiento de imágenes** (thumbnails, compresión).
  Fuera de alcance del framework — es lógica de aplicación específica de
  cada proyecto.
- **Cuotas de almacenamiento por usuario/negocio.** Se resuelve a nivel
  de aplicación (contando `SUM(size)` en la tabla de metadata), no en
  `Storage`.
