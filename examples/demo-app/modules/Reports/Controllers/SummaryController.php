<?php

declare(strict_types=1);

namespace Modules\Reports\Controllers;

use Misi\Http\JsonResponse;
use Misi\Http\Request;

/**
 * Resumen simple del negocio: cuántos clientes y pedidos hay, y cuántos
 * pedidos hay por estado. Usa app()->database() exactamente igual que
 * cualquier controlador de app/ — un módulo no tiene un API distinta.
 */
final class SummaryController
{
    public function __invoke(Request $request): JsonResponse
    {
        $db = app()->database();

        $totalCustomers = (int) ($db->selectOne('SELECT COUNT(*) AS total FROM customers')['total'] ?? 0);
        $totalOrders = (int) ($db->selectOne('SELECT COUNT(*) AS total FROM orders')['total'] ?? 0);

        $byStatus = $db->select(
            'SELECT status, COUNT(*) AS total FROM orders GROUP BY status'
        );

        return JsonResponse::success([
            'total_customers' => $totalCustomers,
            'total_orders' => $totalOrders,
            'orders_by_status' => $byStatus,
        ]);
    }
}
