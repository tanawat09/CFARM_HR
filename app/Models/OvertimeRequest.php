<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OvertimeRequest extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    protected $fillable = ['employee_id', 'date', 'planned_start', 'planned_end', 'planned_hours', 'reason', 'status', 'approved_by', 'approved_at', 'approval_note', 'actual_hours'];
    protected function casts(): array {
        return ['date' => 'date', 'planned_hours' => 'decimal:1', 'actual_hours' => 'decimal:1', 'approved_at' => 'datetime', 'status' => \App\Enums\OvertimeStatus::class];
    }
    public function employee() { return $this->belongsTo(Employee::class); }
    public function approver() { return $this->belongsTo(Employee::class, 'approved_by'); }
}
