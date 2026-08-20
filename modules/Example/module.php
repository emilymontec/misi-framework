<?php

declare(strict_types=1);

/**
 * Descriptor del módulo Example.
 *
 * Este módulo existe únicamente como referencia viva de la
 * infraestructura de módulos (Fase 10) — demuestra que Application
 * descubre module.php, engancha routes.php y que el Migrator recorre
 * migrations/ con el prefijo "Example/" automáticamente. No es un
 * módulo de negocio real: al crear tu primer módulo real, puedes
 * copiar esta carpeta como punto de partida y luego borrar este
 * comentario.
 *
 * Contrato mínimo que Application espera de un module.php:
 *   - 'name' (string, obligatorio): identifica al módulo. Se usa como
 *     prefijo de sus migraciones en la tabla `migrations`
 *     ("Example/001_....php"), así que debe ser único entre módulos.
 *   - 'routes' (string|null): ruta absoluta a un archivo que registra
 *     rutas con $router, igual que routes/web.php. Omite la clave o usa
 *     null si el módulo no expone rutas propias.
 *   - 'migrations' (string|null): ruta absoluta a una carpeta con
 *     migraciones (mismo formato que database/migrations/). Omite la
 *     clave o usa null si el módulo no tiene tablas propias.
 *
 * No hay más contrato que este. No hay sistema de eventos/hooks: si tu
 * módulo necesita más que rutas y migraciones (ej. comandos de consola
 * propios), se evalúa agregarlo cuando un módulo real lo necesite.
 */
return [
    'name' => 'Example',
    'routes' => __DIR__ . '/routes.php',
    'migrations' => __DIR__ . '/migrations',
];
