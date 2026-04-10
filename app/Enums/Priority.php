<?php

namespace App\Enums;

enum Priority: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case URGENT = 'urgent';

    public function label(): string
    {
        return match($this) {
            self::LOW => 'ต่ำ',
            self::MEDIUM => 'ปานกลาง',
            self::HIGH => 'สูง',
            self::URGENT => 'ด่วนมาก',
        };
    }
    
    public function color(): string
    {
         return match($this) {
            self::LOW => 'gray',
            self::MEDIUM => 'blue',
            self::HIGH => 'orange',
            self::URGENT => 'red',
        };
    }
}
