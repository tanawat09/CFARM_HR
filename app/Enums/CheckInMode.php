<?php

namespace App\Enums;

enum CheckInMode: string
{
    case GEOFENCE = 'geofence';
    case FREE = 'free';
    case MULTI_SITE = 'multi_site';

    public function label(): string
    {
        return match($this) {
            self::GEOFENCE => 'ตรวจสอบพื้นที่จำกัด',
            self::FREE => 'ทำรายการที่ไหนก็ได้',
            self::MULTI_SITE => 'หลายไซต์งาน',
        };
    }
}
