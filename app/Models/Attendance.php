<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    protected $fillable = ['employee_id', 'date', 'shift_id', 'check_in_at', 'check_in_latitude', 'check_in_longitude', 'check_in_accuracy', 'check_in_address', 'check_in_photo', 'check_in_note', 'check_in_worksite_id', 'check_in_within_geofence', 'check_in_outside_reason', 'check_out_at', 'check_out_latitude', 'check_out_longitude', 'check_out_accuracy', 'check_out_address', 'check_out_photo', 'check_out_note', 'check_out_worksite_id', 'check_out_within_geofence', 'check_out_outside_reason', 'status', 'is_late', 'late_minutes', 'is_early_leave', 'early_leave_minutes', 'working_minutes', 'ot_minutes', 'is_outside_geofence', 'device_info', 'ip_address', 'is_amended'];
    protected function casts(): array {
        return [
            'date' => 'date', 'check_in_at' => 'datetime', 'check_out_at' => 'datetime',
            'check_in_latitude' => 'decimal:8', 'check_in_longitude' => 'decimal:8', 'check_in_accuracy' => 'decimal:2',
            'check_out_latitude' => 'decimal:8', 'check_out_longitude' => 'decimal:8', 'check_out_accuracy' => 'decimal:2',
            'check_in_within_geofence' => 'boolean', 'check_out_within_geofence' => 'boolean',
            'is_late' => 'boolean', 'is_early_leave' => 'boolean', 'is_outside_geofence' => 'boolean', 'is_amended' => 'boolean',
            'status' => \App\Enums\AttendanceStatus::class
        ];
    }
    public function employee() { return $this->belongsTo(Employee::class); }
    public function shift() { return $this->belongsTo(Shift::class); }
    public function checkInWorksite() { return $this->belongsTo(Worksite::class, 'check_in_worksite_id'); }
    public function checkOutWorksite() { return $this->belongsTo(Worksite::class, 'check_out_worksite_id'); }
    public function activities() { return $this->hasMany(AttendanceActivity::class); }
}
