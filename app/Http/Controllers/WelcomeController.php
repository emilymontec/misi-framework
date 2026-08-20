<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Misi\Http\Request;
use Misi\Http\Response;

final class WelcomeController
{
    public function index(Request $request): Response
    {
        $html = <<<HTML
        <!doctype html>
        <html lang="es">
        <head>
            <meta charset="utf-8">
            <title>Misi</title>
        </head>
        <body style="font-family: sans-serif; max-width: 640px; margin: 60px auto;">
            <h1>Misi está funcionando 🎉</h1>
            <p>Esta es la aplicación base. Edita <code>routes/web.php</code> y
            <code>app/Http/Controllers</code> para comenzar tu proyecto.</p>
            <ul>
                <li><a href="/api/ping">/api/ping</a> (respuesta JSON)</li>
                <li><a href="/saludo/Misi">/saludo/Misi</a> (parámetro de ruta)</li>
                <li><a href="/ui-kit">/ui-kit</a> (componentes UI: buttons, forms, tablas, alerts, modal...)</li>
            </ul>
        </body>
        </html>
        HTML;

        return new Response($html);
    }

    public function greet(Request $request, string $name): Response
    {
        return new Response("<h1>Hola, " . htmlspecialchars($name) . "!</h1>");
    }
}
