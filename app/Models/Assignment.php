<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    use \Illuminate\Database\Eloquent\SoftDeletes;
    protected $fillable = ['title', 'description', 'employee_id', 'assigned_by', 'worksite_id', 'scheduled_date', 'scheduled_time', 'due_date', 'priority', 'status', 'completion_note', 'before_photo', 'after_photo', 'completed_at'];
    protected function casts(): array {
        return ['scheduled_date' => 'date', 'due_date' => 'date', 'completed_at' => 'datetime', 'priority' => \App\Enums\Priority::class, 'status' => \App\Enums\AssignmentStatus::class];
    }
    public function employee() { return $this->belongsTo(Employee::class); }
    public function assigner() { return $this->belongsTo(Employee::class, 'assigned_by'); }
    public function worksite() { return $this->belongsTo(Worksite::class); }
}
