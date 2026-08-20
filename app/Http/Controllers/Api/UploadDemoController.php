<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Misi\Auth\Auth;
use Misi\Exceptions\NotFoundException;
use Misi\Http\JsonResponse;
use Misi\Http\Request;
use Misi\Http\Response;

/**
 * Controlador de demostración del sistema de Storage (Fase 8).
 * No es parte del framework: vive en app/ como ejemplo de uso.
 */
final class UploadDemoController
{
    public function store(Request $request): JsonResponse
    {
        $data = app()->validator()->validate([
            'file' => $request->file('file'),
        ], [
            'file' => ['required', 'image', 'mimes:jpg,jpeg,png,gif', 'max_size:2048'],
        ]);

        $storage = app()->storage();
        $path = $storage->putUploadedFile($data['file'], 'avatars');

        $id = app()->database()->insert('uploads', [
            'path' => $path,
            'original_name' => $data['file']['name'],
            'mime_type' => $storage->mimeType($path),
            'size' => $storage->size($path),
            'uploaded_by' => Auth::id(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return JsonResponse::success([
            'id' => $id,
            'path' => $path,
            'url' => $storage->url($path),
        ], 'Archivo subido', 201);
    }

    /**
     * Sirve el archivo. Público en esta demo a propósito (piénsalo como
     * un avatar); si tu proyecto guarda documentos privados, agrega el
     * middleware 'auth' a esta ruta (ver docs/storage.md).
     */
    public function show(Request $request, string $path): Response
    {
        $storage = app()->storage();

        if (!$storage->exists($path)) {
            throw new NotFoundException("Archivo no encontrado: {$path}");
        }

        return (new Response($storage->get($path)))
            ->header('Content-Type', $storage->mimeType($path));
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $upload = app()->database()->selectOne('SELECT * FROM uploads WHERE id = ?', [$id]);

        if ($upload === null) {
            throw new NotFoundException('Archivo no encontrado.');
        }

        app()->storage()->delete($upload['path']);
        app()->database()->delete('uploads', 'id = ?', [$id]);

        return JsonResponse::success(null, 'Archivo eliminado');
    }
}
