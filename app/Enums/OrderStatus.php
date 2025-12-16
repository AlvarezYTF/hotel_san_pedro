<?php

namespace App\Enums;

enum OrderStatus: int
{
    case PENDIENTE = 0;
    case VENDIDO = 1;
    case CANCELADO = 2;

    public function label(): string
    {
        return match ($this) {
            self::PENDIENTE => __('Pendiente'),
            self::VENDIDO => __('Vendido'),
            self::CANCELADO => __('Cancelado')
        };
    }
}
