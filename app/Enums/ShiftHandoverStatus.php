<?php

namespace App\Enums;

enum ShiftHandoverStatus: string
{
    case ACTIVE = 'activo';
    case DELIVERED = 'entregado';
    case RECEIVED = 'recibido';
    case CLOSED = 'cerrado';
}

