<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeavePolicy extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'key', 'name', 'icon', 'quota_days',
        'requires_reason', 'requires_attachment_after_days',
        'probation_allowed', 'is_tenure_based',
        'tenure_threshold_years', 'tenure_bonus_days',
        'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'requires_reason' => 'boolean',
            'probation_allowed' => 'boolean',
            'is_tenure_based' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
