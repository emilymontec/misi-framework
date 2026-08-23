<?php

declare(strict_types=1);

/**
 * Descriptor del módulo Catalog (Fase 17).
 *
 * Primer módulo de negocio real de Misi, y deliberadamente genérico:
 * no es "Modules\Ropa" ni "Modules\Bordados" (esos siguen sin construirse
 * por falta de evidencia real — ver ROADMAP.md). Catálogo + inventario +
 * acceso admin es necesario para cualquier negocio que venda productos,
 * independientemente de qué producto sea — es la base sobre la que
 * después SÍ podría construirse un "Modules\Ropa" (agregando variantes
 * de talla/color) cuando un proyecto real de ropa lo pida.
 *
 * Sin migraciones propias: las tablas (`categories`, `products`) son de
 * Business Core (`business/migrations/`), no de este módulo — Catalog
 * solo agrega la capa de acceso (rutas + permisos + panel admin) sobre
 * datos que ya vive en Business Core. Ver docs/business-core.md.
 */
return [
    'name' => 'Catalog',
    'routes' => __DIR__ . '/routes.php',
    'migrations' => null,
];
