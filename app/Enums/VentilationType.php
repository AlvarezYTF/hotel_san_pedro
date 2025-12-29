<?php

namespace App\Enums;

enum VentilationType: string
{
    case VENTILADOR = 'ventilador';
    case AIRE_ACONDICIONADO = 'aire_acondicionado';

    public function label(): string
    {
        return match ($this) {
            self::VENTILADOR => 'Ventilador',
            self::AIRE_ACONDICIONADO => 'Aire Acondicionado',
        };
    }
}

