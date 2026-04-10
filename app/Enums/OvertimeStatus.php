<?php

namespace App\Enums;

enum OvertimeStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'รออนุมัติ',
            self::APPROVED => 'อนุมัติ',
            self::REJECTED => 'ไม่อนุมัติ',
            self::CANCELLED => 'ยกเลิก',
        };
    }
}
