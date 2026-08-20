<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Misi\Http\Response;

/**
 * Sirve resources/views/ui-kit.php — la referencia viva de los
 * componentes de la Fase 13 (public/css/misi.css + public/js/*.js).
 *
 * No hay motor de plantillas todavía: se incluye el archivo con
 * output buffering, igual que cualquier vista PHP tradicional. Si en el
 * futuro se necesita pasar variables a la vista, este es el punto donde
 * se haría un extract() antes del require — no aplica todavía porque
 * ui-kit.php es contenido estático.
 */
final class UiKitController
{
    public function index(): Response
    {
        $viewPath = app()->basePath . '/resources/views/ui-kit.php';

        ob_start();
        require $viewPath;
        $html = ob_get_clean();

        return new Response($html !== false ? $html : '');
    }
}
