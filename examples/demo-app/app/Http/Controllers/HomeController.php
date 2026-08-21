<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Misi\Http\Response;

final class HomeController
{
    public function index(): Response
    {
        $viewPath = app()->basePath . '/resources/views/app.php';

        ob_start();
        require $viewPath;
        $html = ob_get_clean();

        return new Response($html !== false ? $html : '');
    }
}
