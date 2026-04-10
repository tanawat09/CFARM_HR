<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    protected $fillable = ['employee_id', 'leave_type', 'leave_format', 'start_date', 'end_date', 'start_time', 'end_time', 'total_days', 'reason', 'status', 'approved_by', 'approved_at', 'approval_note', 'attachment_path'];
    protected function casts(): array {
        return [
            'start_date' => 'date', 
            'end_date' => 'date', 
            'total_days' => 'decimal:1', 
            'approved_at' => 'datetime', 
            'status' => \App\Enums\LeaveStatus::class,
            'leave_type' => \App\Enums\LeaveType::class
        ];
    }
    public function employee() { return $this->belongsTo(Employee::class); }
    public function approver() { return $this->belongsTo(Employee::class, 'approved_by'); }
}
