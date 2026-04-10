<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeAmendment extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    protected $fillable = ['attendance_id', 'employee_id', 'amendment_type', 'original_check_in', 'original_check_out', 'requested_check_in', 'requested_check_out', 'reason', 'status', 'approved_by', 'approved_at', 'approval_note'];
    protected function casts(): array {
        return ['original_check_in' => 'datetime', 'original_check_out' => 'datetime', 'requested_check_in' => 'datetime', 'requested_check_out' => 'datetime', 'approved_at' => 'datetime'];
    }
    public function attendance() { return $this->belongsTo(Attendance::class); }
    public function employee() { return $this->belongsTo(Employee::class); }
    public function approver() { return $this->belongsTo(Employee::class, 'approved_by'); }
}
