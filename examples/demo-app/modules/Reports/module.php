<?php

declare(strict_types=1);

/**
 * Módulo de reportes de la demo (Fase 14). Demuestra la infraestructura
 * de módulos (Fase 10) resolviendo algo real, no solo un ping — un
 * resumen de negocio que combina datos de dos tablas (customers, orders)
 * que viven fuera del módulo mismo.
 */
return [
    'name' => 'Reports',
    'routes' => __DIR__ . '/routes.php',
    'migrations' => null, // este módulo no tiene tablas propias
];
