<?php

namespace App\Enums;

enum ShiftProductOutReason: string
{
    case CONSUMO_INTERNO = 'consumo_interno';
    case MERMA = 'merma';
    case DONACION = 'donacion';
    case PERDIDA = 'perdida';
    case AJUSTE_INVENTARIO = 'ajuste_inventario';
    case OTRO = 'otro';

    public function label(): string
    {
        return match($this) {
            self::CONSUMO_INTERNO => 'Consumo Interno',
            self::MERMA => 'Merma / Daño',
            self::DONACION => 'Donación',
            self::PERDIDA => 'Pérdida / Robo',
            self::AJUSTE_INVENTARIO => 'Ajuste de Inventario',
            self::OTRO => 'Otro Motivo',
        };
    }
}

