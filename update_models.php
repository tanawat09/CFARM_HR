<?php

$models = [
    'Department' => <<<PHP
    protected \$fillable = ['name', 'code', 'is_active'];
    protected function casts(): array { return ['is_active' => 'boolean']; }
    public function employees() { return \$this->hasMany(Employee::class); }
PHP,
    'Position' => <<<PHP
    protected \$fillable = ['name', 'department_id', 'is_active'];
    protected function casts(): array { return ['is_active' => 'boolean']; }
    public function department() { return \$this->belongsTo(Department::class); }
    public function employees() { return \$this->hasMany(Employee::class); }
PHP,
    'Branch' => <<<PHP
    protected \$fillable = ['name', 'code', 'address', 'latitude', 'longitude', 'is_active'];
    protected function casts(): array { 
        return ['is_active' => 'boolean', 'latitude' => 'decimal:8', 'longitude' => 'decimal:8']; 
    }
    public function employees() { return \$this->hasMany(Employee::class); }
    public function worksites() { return \$this->hasMany(Worksite::class); }
PHP,
    'Shift' => <<<PHP
    protected \$fillable = ['name', 'code', 'start_time', 'end_time', 'late_after_minutes', 'early_leave_before_minutes', 'ot_after_minutes', 'break_duration_minutes', 'working_days', 'is_active'];
    protected function casts(): array { 
        return ['is_active' => 'boolean', 'working_days' => 'array']; 
    }
    public function employees() { return \$this->hasMany(Employee::class); }
PHP,
    'Employee' => <<<PHP
    use \Illuminate\Database\Eloquent\SoftDeletes;
    protected \$fillable = ['user_id', 'employee_code', 'first_name', 'last_name', 'phone', 'department_id', 'position_id', 'branch_id', 'supervisor_id', 'shift_id', 'employment_status', 'check_in_mode', 'profile_photo', 'hire_date'];
    protected function casts(): array { 
        return [
            'hire_date' => 'date', 
            'employment_status' => \App\Enums\EmploymentStatus::class,
            'check_in_mode' => \App\Enums\CheckInMode::class
        ]; 
    }
    public function user() { return \$this->belongsTo(User::class); }
    public function department() { return \$this->belongsTo(Department::class); }
    public function position() { return \$this->belongsTo(Position::class); }
    public function branch() { return \$this->belongsTo(Branch::class); }
    public function supervisor() { return \$this->belongsTo(Employee::class, 'supervisor_id'); }
    public function shift() { return \$this->belongsTo(Shift::class); }
    public function subordinates() { return \$this->hasMany(Employee::class, 'supervisor_id'); }
    public function worksites() { return \$this->belongsToMany(Worksite::class)->withPivot('assigned_from', 'assigned_to')->withTimestamps(); }
    public function attendances() { return \$this->hasMany(Attendance::class); }
PHP,
    'Worksite' => <<<PHP
    use \Illuminate\Database\Eloquent\SoftDeletes;
    protected \$fillable = ['name', 'code', 'address', 'latitude', 'longitude', 'radius_meters', 'geofence_type', 'polygon_coordinates', 'is_active', 'branch_id'];
    protected function casts(): array { 
        return ['is_active' => 'boolean', 'latitude' => 'decimal:8', 'longitude' => 'decimal:8', 'polygon_coordinates' => 'array']; 
    }
    public function branch() { return \$this->belongsTo(Branch::class); }
    public function employees() { return \$this->belongsToMany(Employee::class)->withPivot('assigned_from', 'assigned_to')->withTimestamps(); }
PHP,
    'WorksiteEmployee' => <<<PHP
    protected \$table = 'worksite_employee';
    protected \$fillable = ['worksite_id', 'employee_id', 'assigned_from', 'assigned_to'];
    protected function casts(): array { return ['assigned_from' => 'date', 'assigned_to' => 'date']; }
PHP,
    'Attendance' => <<<PHP
    protected \$fillable = ['employee_id', 'date', 'shift_id', 'check_in_at', 'check_in_latitude', 'check_in_longitude', 'check_in_accuracy', 'check_in_address', 'check_in_photo', 'check_in_note', 'check_in_worksite_id', 'check_in_within_geofence', 'check_in_outside_reason', 'check_out_at', 'check_out_latitude', 'check_out_longitude', 'check_out_accuracy', 'check_out_address', 'check_out_photo', 'check_out_note', 'check_out_worksite_id', 'check_out_within_geofence', 'check_out_outside_reason', 'status', 'is_late', 'late_minutes', 'is_early_leave', 'early_leave_minutes', 'working_minutes', 'ot_minutes', 'is_outside_geofence', 'device_info', 'ip_address', 'is_amended'];
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
    public function employee() { return \$this->belongsTo(Employee::class); }
    public function shift() { return \$this->belongsTo(Shift::class); }
    public function checkInWorksite() { return \$this->belongsTo(Worksite::class, 'check_in_worksite_id'); }
    public function checkOutWorksite() { return \$this->belongsTo(Worksite::class, 'check_out_worksite_id'); }
    public function activities() { return \$this->hasMany(AttendanceActivity::class); }
PHP,
    'AttendanceActivity' => <<<PHP
    protected \$fillable = ['attendance_id', 'activity_type', 'timestamp', 'latitude', 'longitude', 'accuracy', 'photo', 'note', 'worksite_id'];
    protected function casts(): array {
        return ['timestamp' => 'datetime', 'latitude' => 'decimal:8', 'longitude' => 'decimal:8', 'accuracy' => 'decimal:2'];
    }
    public function attendance() { return \$this->belongsTo(Attendance::class); }
    public function worksite() { return \$this->belongsTo(Worksite::class); }
PHP,
    'Assignment' => <<<PHP
    use \Illuminate\Database\Eloquent\SoftDeletes;
    protected \$fillable = ['title', 'description', 'employee_id', 'assigned_by', 'worksite_id', 'scheduled_date', 'scheduled_time', 'due_date', 'priority', 'status', 'completion_note', 'before_photo', 'after_photo', 'completed_at'];
    protected function casts(): array {
        return ['scheduled_date' => 'date', 'due_date' => 'date', 'completed_at' => 'datetime', 'priority' => \App\Enums\Priority::class, 'status' => \App\Enums\AssignmentStatus::class];
    }
    public function employee() { return \$this->belongsTo(Employee::class); }
    public function assigner() { return \$this->belongsTo(Employee::class, 'assigned_by'); }
    public function worksite() { return \$this->belongsTo(Worksite::class); }
PHP,
    'LeaveType' => <<<PHP
    protected \$fillable = ['name', 'code', 'default_days_per_year', 'is_paid', 'is_active'];
    protected function casts(): array { return ['is_paid' => 'boolean', 'is_active' => 'boolean']; }
PHP,
    'LeaveRequest' => <<<PHP
    protected \$fillable = ['employee_id', 'leave_type_id', 'start_date', 'end_date', 'total_days', 'reason', 'status', 'approved_by', 'approved_at', 'approval_note'];
    protected function casts(): array {
        return ['start_date' => 'date', 'end_date' => 'date', 'total_days' => 'decimal:1', 'approved_at' => 'datetime', 'status' => \App\Enums\LeaveStatus::class];
    }
    public function employee() { return \$this->belongsTo(Employee::class); }
    public function leaveType() { return \$this->belongsTo(LeaveType::class); }
    public function approver() { return \$this->belongsTo(Employee::class, 'approved_by'); }
PHP,
    'LeaveBalance' => <<<PHP
    protected \$fillable = ['employee_id', 'leave_type_id', 'year', 'entitled_days', 'used_days', 'remaining_days'];
    protected function casts(): array { return ['entitled_days' => 'decimal:1', 'used_days' => 'decimal:1', 'remaining_days' => 'decimal:1']; }
    public function employee() { return \$this->belongsTo(Employee::class); }
    public function leaveType() { return \$this->belongsTo(LeaveType::class); }
PHP,
    'OvertimeRequest' => <<<PHP
    protected \$fillable = ['employee_id', 'date', 'planned_start', 'planned_end', 'planned_hours', 'reason', 'status', 'approved_by', 'approved_at', 'approval_note', 'actual_hours'];
    protected function casts(): array {
        return ['date' => 'date', 'planned_hours' => 'decimal:1', 'actual_hours' => 'decimal:1', 'approved_at' => 'datetime', 'status' => \App\Enums\OvertimeStatus::class];
    }
    public function employee() { return \$this->belongsTo(Employee::class); }
    public function approver() { return \$this->belongsTo(Employee::class, 'approved_by'); }
PHP,
    'TimeAmendment' => <<<PHP
    protected \$fillable = ['attendance_id', 'employee_id', 'amendment_type', 'original_check_in', 'original_check_out', 'requested_check_in', 'requested_check_out', 'reason', 'status', 'approved_by', 'approved_at', 'approval_note'];
    protected function casts(): array {
        return ['original_check_in' => 'datetime', 'original_check_out' => 'datetime', 'requested_check_in' => 'datetime', 'requested_check_out' => 'datetime', 'approved_at' => 'datetime'];
    }
    public function attendance() { return \$this->belongsTo(Attendance::class); }
    public function employee() { return \$this->belongsTo(Employee::class); }
    public function approver() { return \$this->belongsTo(Employee::class, 'approved_by'); }
PHP,
    'Holiday' => <<<PHP
    protected \$fillable = ['name', 'date', 'is_recurring', 'branch_id'];
    protected function casts(): array { return ['date' => 'date', 'is_recurring' => 'boolean']; }
    public function branch() { return \$this->belongsTo(Branch::class); }
PHP,
    'Setting' => <<<PHP
    protected \$fillable = ['key', 'value', 'group'];
PHP,
    'AuditLog' => <<<PHP
    protected \$fillable = ['user_id', 'action', 'auditable_type', 'auditable_id', 'old_values', 'new_values', 'ip_address', 'user_agent'];
    protected function casts(): array { return ['old_values' => 'array', 'new_values' => 'array']; }
    public function user() { return \$this->belongsTo(User::class); }
    public function auditable() { return \$this->morphTo(); }
PHP,
];

$dir = __DIR__ . '/app/Models/';

foreach ($models as $model => $body) {
    if ($model === 'User') continue;
    $file = $dir . $model . '.php';
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        $pattern = '/class ' . $model . ' extends Model\n\{.*?\n\}/s';
        $replacement = "class $model extends Model\n{\n    use \Illuminate\Database\Eloquent\Factories\HasFactory;\n$body\n}";
        
        $newContent = preg_replace($pattern, $replacement, $content);
        file_put_contents($file, $newContent);
    }
}
echo "Models updated.";
