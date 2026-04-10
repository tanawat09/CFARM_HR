<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    protected $fillable = ['name', 'code', 'start_time', 'end_time', 'late_after_minutes', 'early_leave_before_minutes', 'ot_after_minutes', 'break_duration_minutes', 'working_days', 'is_active'];
    protected function casts(): array { 
        return ['is_active' => 'boolean', 'working_days' => 'array']; 
    }
    public function employees() { return $this->hasMany(Employee::class); }
}
