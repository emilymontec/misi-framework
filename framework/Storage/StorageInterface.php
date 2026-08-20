<?php

declare(strict_types=1);

namespace Misi\Storage;

/**
 * Abstracción de almacenamiento de archivos.
 *
 * `LocalStorage` es la única implementación por ahora. Esta interfaz
 * prepara el terreno para `S3Storage`/`CloudStorage` futuros (🧊
 * congelado, no se implementan todavía — no hay necesidad real hoy).
 *
 * Los archivos NUNCA se guardan en MySQL como BLOB: solo se guarda su
 * metadata (ruta, mime, tamaño, propietario). El archivo en sí vive en
 * disco (o, en el futuro, en el proveedor cloud que corresponda).
 */
interface StorageInterface
{
    /** Escribe contenido crudo en una ruta exacta (la sobrescribe si ya existe). */
    public function put(string $path, string $contents): bool;

    /**
     * Guarda de forma segura un archivo subido (estructura de $_FILES:
     * tmp_name, name, error...). Genera un nombre nuevo (nunca reutiliza
     * el original) y retorna la ruta relativa donde quedó guardado —
     * esa ruta es lo que el proyecto persiste como metadata en su propia
     * tabla.
     *
     * @param array<string, mixed> $uploadedFile
     */
    public function putUploadedFile(array $uploadedFile, string $directory = ''): string;

    public function get(string $path): string;

    public function delete(string $path): bool;

    public function exists(string $path): bool;

    /** URL pública (o ruta) para acceder al archivo. Ver docs/storage.md sobre cómo servirlo. */
    public function url(string $path): string;

    public function size(string $path): int;

    public function mimeType(string $path): string;
}
