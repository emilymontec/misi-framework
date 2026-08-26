<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Misi\Http\Request;
use Misi\Http\Response;

/**
 * Sirve resources/views/welcome.php — la página de bienvenida con el
 * diseño de marca de Misi (misma paleta que la landing del framework,
 * index.html). Igual que UiKitController: sin motor de plantillas,
 * solo un require con output buffering (ver el docblock de
 * UiKitController para el razonamiento completo).
 *
 * No es parte del framework en sí: es contenido de plantilla que
 * "misi new" copia a cada proyecto nuevo para que tenga algo que
 * mostrar en "/" desde el primer minuto — reemplázala libremente.
 */
final class WelcomeController
{
    public function index(Request $request): Response
    {
        $viewPath = app()->basePath . '/resources/views/welcome.php';

        ob_start();
        require $viewPath;
        $html = ob_get_clean();

        return new Response($html !== false ? $html : '');
    }

    public function greet(Request $request, string $name): Response
    {
        return new Response('<h1>Hola, ' . htmlspecialchars($name) . '!</h1>');
    }
}
