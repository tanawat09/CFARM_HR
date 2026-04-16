<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case HR = 'hr';
    case MANAGING_DIRECTOR = 'managing_director';
    case DEPUTY_MD = 'deputy_md';
    case MANAGER = 'manager';
    case SUPERVISOR = 'supervisor';
    case EMPLOYEE = 'employee';

    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Admin',
            self::HR => 'HR',
            self::MANAGING_DIRECTOR => 'กรรมการผู้จัดการ',
            self::DEPUTY_MD => 'รองกรรมการ',
            self::MANAGER => 'ผู้จัดการ',
            self::SUPERVISOR => 'หัวหน้างาน',
            self::EMPLOYEE => 'พนักงาน',
        };
    }
}
