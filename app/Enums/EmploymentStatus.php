<?php

namespace App\Enums;

enum EmploymentStatus: string
{
    case ACTIVE = 'active';
    case PROBATION = 'probation';
    case SUSPENDED = 'suspended';
    case RESIGNED = 'resigned';
    case TERMINATED = 'terminated';

    public function label(): string
    {
        return match($this) {
            self::ACTIVE => 'ทำงานปกติ',
            self::PROBATION => 'ทดลองงาน',
            self::SUSPENDED => 'พักงาน',
            self::RESIGNED => 'ลาออก',
            self::TERMINATED => 'เลิกจ้าง',
        };
    }
}
