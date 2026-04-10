<?php

namespace App\Enums;

enum AssignmentStatus: string
{
    case ASSIGNED = 'assigned';
    case ACCEPTED = 'accepted';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::ASSIGNED => 'มอบหมายแล้ว',
            self::ACCEPTED => 'รับงานแล้ว',
            self::IN_PROGRESS => 'กำลังดำเนินการ',
            self::COMPLETED => 'เสร็จสิ้น',
            self::CANCELLED => 'ยกเลิก',
        };
    }
}
