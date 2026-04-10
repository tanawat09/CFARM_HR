<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceActivity extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    protected $fillable = ['attendance_id', 'activity_type', 'timestamp', 'latitude', 'longitude', 'accuracy', 'photo', 'note', 'worksite_id'];
    protected function casts(): array {
        return ['timestamp' => 'datetime', 'latitude' => 'decimal:8', 'longitude' => 'decimal:8', 'accuracy' => 'decimal:2'];
    }
    public function attendance() { return $this->belongsTo(Attendance::class); }
    public function worksite() { return $this->belongsTo(Worksite::class); }
}
