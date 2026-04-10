<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    use \Illuminate\Database\Eloquent\SoftDeletes;
    protected $fillable = ['user_id', 'employee_code', 'first_name', 'last_name', 'phone', 'department_id', 'position_id', 'branch_id', 'supervisor_id', 'shift_id', 'employment_status', 'check_in_mode', 'profile_photo', 'hire_date'];
    
    protected static function booted()
    {
        static::saved(function ($employee) {
            $employee->syncUserRole();
        });
    }

    protected function casts(): array { 
        return [
            'hire_date' => 'date', 
            'employment_status' => \App\Enums\EmploymentStatus::class,
            'check_in_mode' => \App\Enums\CheckInMode::class
        ]; 
    }

    /**
     * Sync user role based on position name and hierarchy.
     */
    public function syncUserRole()
    {
        $user = $this->user;
        if (!$user) return;

        // Refresh position in case it was just changed
        $position = $this->position()->first();
        if (!$position) return;

        $posName = $position->name;
        $currentRole = $user->role;
        $targetRole = null;

        // 1. Check for Administrative / Management Positions
        if ($posName === 'HR Manager') {
            $targetRole = \App\Enums\UserRole::HR;
        } elseif ($posName === 'IT Admin' || str_contains($posName, 'กรรมการผู้จัดการ')) {
            $targetRole = \App\Enums\UserRole::ADMIN;
        } elseif (str_contains($posName, 'ผู้จัดการฝ่าย') || str_contains($posName, 'ผู้จัดการฟาร์ม') || $this->subordinates()->exists()) {
            // Only upgrade to Supervisor if they are not already Admin/HR
            if (!in_array($currentRole, [\App\Enums\UserRole::ADMIN, \App\Enums\UserRole::HR])) {
                $targetRole = \App\Enums\UserRole::SUPERVISOR;
            }
        }

        if ($targetRole && $currentRole !== $targetRole) {
            $user->update(['role' => $targetRole]);
        }
    }
    public function user() { return $this->belongsTo(User::class); }
    public function department() { return $this->belongsTo(Department::class); }
    public function position() { return $this->belongsTo(Position::class); }
    public function branch() { return $this->belongsTo(Worksite::class, 'branch_id'); }
    public function supervisor() { return $this->belongsTo(Employee::class, 'supervisor_id'); }
    public function shift() { return $this->belongsTo(Shift::class); }
    public function subordinates() { return $this->hasMany(Employee::class, 'supervisor_id'); }
    public function worksites() { return $this->belongsToMany(Worksite::class, 'worksite_employee')->withPivot('assigned_from', 'assigned_to')->withTimestamps(); }
    public function attendances() { return $this->hasMany(Attendance::class); }
}
