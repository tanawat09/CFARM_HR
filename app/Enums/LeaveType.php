<?php

namespace App\Enums;

enum LeaveType: string
{
    case ANNUAL = 'annual';
    case SICK = 'sick';
    case PERSONAL = 'personal';
    case MATERNITY = 'maternity';
    case OTHER = 'other';
}
