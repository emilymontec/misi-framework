<?php

declare(strict_types=1);

namespace Misi\Business\Products;

use Misi\Exceptions\HttpException;

/**
 * Lanzada por ProductRepository::adjustStock() cuando un ajuste dejaría
 * stock_quantity en negativo. 422, igual criterio que ValidationException:
 * es un error del cliente de la API (intentó descontar más stock del
 * disponible), no un fallo del servidor.
 */
final class InsufficientStockException extends HttpException
{
    public function __construct(int $productId, int $available, int $requested)
    {
        parent::__construct(
            422,
            "Stock insuficiente para el producto #{$productId}: disponible {$available}, solicitado {$requested}."
        );
    }
}
