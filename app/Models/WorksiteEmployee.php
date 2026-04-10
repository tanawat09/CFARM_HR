<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class WorksiteEmployee extends Pivot
{
    protected $table = 'worksite_employee';
    protected $fillable = ['worksite_id', 'employee_id', 'assigned_from', 'assigned_to'];

    protected function casts(): array {
        return [
            'assigned_from' => 'date',
            'assigned_to' => 'date',
        ];
    }
}
