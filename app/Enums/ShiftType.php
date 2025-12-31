<?php

namespace App\Enums;

enum ShiftType: string
{
    case DAY = 'dia';
    case NIGHT = 'noche';
    case ADMIN = 'admin';
}

