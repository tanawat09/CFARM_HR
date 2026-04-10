<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case CHECKED_IN = 'checked_in';
    case CHECKED_OUT = 'checked_out';
    case MISSED_CHECKOUT = 'missed_checkout';
    case ABSENT = 'absent';
    case LEAVE = 'leave';
    case HOLIDAY = 'holiday';

    public function label(): string
    {
        return match($this) {
            self::CHECKED_IN => 'เข้างานแล้ว',
            self::CHECKED_OUT => 'เลิกงานแล้ว',
            self::MISSED_CHECKOUT => 'ลืมลงเวลาออก',
            self::ABSENT => 'ขาดงาน',
            self::LEAVE => 'ลา',
            self::HOLIDAY => 'วันหยุด',
        };
    }

    public function color(): string
    {
         return match($this) {
            self::CHECKED_IN => 'blue',
            self::CHECKED_OUT => 'green',
            self::MISSED_CHECKOUT => 'red',
            self::ABSENT => 'red',
            self::LEAVE => 'yellow',
            self::HOLIDAY => 'gray',
        };
    }
}
