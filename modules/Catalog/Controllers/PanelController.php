<?php

declare(strict_types=1);

namespace Modules\Catalog\Controllers;

use Misi\Http\Response;

/**
 * Sirve modules/Catalog/Views/panel.php — mismo patrón que
 * app/Http/Controllers/UiKitController.php (Fase 13) y
 * examples/demo-app/app/Http/Controllers/HomeController.php: output
 * buffering sobre una vista PHP plana, sin motor de plantillas.
 */
final class PanelController
{
    public function index(): Response
    {
        $viewPath = __DIR__ . '/../Views/panel.php';

        ob_start();
        require $viewPath;
        $html = ob_get_clean();

        return new Response($html !== false ? $html : '');
    }
}
