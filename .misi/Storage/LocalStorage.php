<?php

declare(strict_types=1);

namespace Misi\Storage;

use Misi\Exceptions\StorageException;

/**
 * Almacenamiento en el filesystem local, bajo un directorio raíz fijo
 * (por defecto storage/uploads/, fuera del document root público — ver
 * docs/storage.md sobre cómo servir estos archivos de forma controlada).
 *
 * Protecciones aplicadas siempre, sin que el proyecto tenga que
 * acordarse de pedirlas:
 *  - Nombre de archivo generado (nunca el original) al guardar uploads.
 *  - Extensión saneada (solo alfanumérica) antes de usarla.
 *  - Bloqueo de path traversal: cualquier ruta con ".." se rechaza.
 *  - Permisos del archivo guardado: 0644 (nunca ejecutable).
 *  - Verificación de `is_uploaded_file()` antes de mover cualquier
 *    archivo subido (defensa en profundidad: Validation, Fase 5, ya
 *    debería haber filtrado esto antes, pero Storage no confía en eso
 *    ciegamente).
 */
final class LocalStorage implements StorageInterface
{
    private readonly string $root;

    public function __construct(string $root)
    {
        $this->root = rtrim($root, '/');
        $this->ensureDirectoryExists($this->root);
    }

    public function put(string $path, string $contents): bool
    {
        $fullPath = $this->fullPath($path);
        $this->ensureDirectoryExists(dirname($fullPath));

        return file_put_contents($fullPath, $contents) !== false;
    }

    public function putUploadedFile(array $uploadedFile, string $directory = ''): string
    {
        if (
            !isset($uploadedFile['tmp_name'], $uploadedFile['error'])
            || $uploadedFile['error'] !== UPLOAD_ERR_OK
            || !is_uploaded_file((string) $uploadedFile['tmp_name'])
        ) {
            throw new StorageException('Archivo subido inválido.', 422);
        }

        $extension = $this->safeExtension((string) ($uploadedFile['name'] ?? ''));
        $filename = bin2hex(random_bytes(16)) . ($extension !== '' ? ".{$extension}" : '');

        $directory = trim($directory, '/');
        $relativePath = $directory !== '' ? "{$directory}/{$filename}" : $filename;

        $fullPath = $this->fullPath($relativePath);
        $this->ensureDirectoryExists(dirname($fullPath));

        if (!move_uploaded_file((string) $uploadedFile['tmp_name'], $fullPath)) {
            throw new StorageException('No fue posible guardar el archivo subido.');
        }

        chmod($fullPath, 0644);

        return $relativePath;
    }

    public function get(string $path): string
    {
        $fullPath = $this->fullPath($path);

        if (!is_file($fullPath)) {
            throw new StorageException("Archivo no encontrado: {$path}", 404);
        }

        $contents = file_get_contents($fullPath);

        return $contents !== false ? $contents : '';
    }

    public function delete(string $path): bool
    {
        $fullPath = $this->fullPath($path);

        return is_file($fullPath) && unlink($fullPath);
    }

    public function exists(string $path): bool
    {
        return is_file($this->fullPath($path));
    }

    /**
     * Ruta pública de referencia (no implica que el archivo sea
     * accesible sin más: el proyecto decide cómo servirlo, ver
     * docs/storage.md).
     */
    public function url(string $path): string
    {
        return '/storage/' . ltrim($path, '/');
    }

    public function size(string $path): int
    {
        $fullPath = $this->fullPath($path);

        if (!is_file($fullPath)) {
            throw new StorageException("Archivo no encontrado: {$path}", 404);
        }

        return (int) filesize($fullPath);
    }

    public function mimeType(string $path): string
    {
        $fullPath = $this->fullPath($path);

        if (!is_file($fullPath)) {
            throw new StorageException("Archivo no encontrado: {$path}", 404);
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo !== false ? finfo_file($finfo, $fullPath) : false;

        if ($finfo !== false) {
            finfo_close($finfo);
        }

        return $mime !== false && $mime !== null ? $mime : 'application/octet-stream';
    }

    /** Resuelve la ruta completa dentro de $root y bloquea path traversal. */
    private function fullPath(string $path): string
    {
        $normalized = str_replace('\\', '/', $path);

        if (str_contains($normalized, '..')) {
            throw new StorageException("Ruta inválida: {$path}", 422);
        }

        return $this->root . '/' . ltrim($normalized, '/');
    }

    private function ensureDirectoryExists(string $dir): void
    {
        if (is_dir($dir)) {
            return;
        }

        if (!mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new StorageException("No fue posible crear el directorio: {$dir}");
        }
    }

    /** Solo alfanumérico: evita trucos como "jpg/../../evil.php" en la extensión. */
    private function safeExtension(string $originalName): string
    {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        return preg_replace('/[^a-z0-9]/', '', $extension) ?? '';
    }
}
