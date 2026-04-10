<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case HR = 'hr';
    case SUPERVISOR = 'supervisor';
    case EMPLOYEE = 'employee';

    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Admin',
            self::HR => 'HR',
            self::SUPERVISOR => 'Supervisor',
            self::EMPLOYEE => 'Employee',
        };
    }
}
