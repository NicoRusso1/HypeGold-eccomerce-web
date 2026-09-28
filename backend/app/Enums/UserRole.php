<?php

namespace App\Enums;

enum UserRole: string
{
    case Cliente = 'cliente';
    case Administrador = 'administrador';
}
