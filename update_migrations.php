<?php

$dir = __DIR__ . '/database/migrations/';
$files = scandir($dir);

$schemas = [
    'departments' => <<<PHP
            \$table->id();
            \$table->string('name', 100)->unique();
            \$table->string('code', 20)->unique();
            \$table->boolean('is_active')->default(true);
            \$table->timestamps();
PHP,
    'positions' => <<<PHP
            \$table->id();
            \$table->string('name', 100);
            \$table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            \$table->boolean('is_active')->default(true);
            \$table->timestamps();
PHP,
    'branches' => <<<PHP
            \$table->id();
            \$table->string('name', 100);
            \$table->string('code', 20)->unique();
            \$table->text('address')->nullable();
            \$table->decimal('latitude', 10, 8)->nullable();
            \$table->decimal('longitude', 11, 8)->nullable();
            \$table->boolean('is_active')->default(true);
            \$table->timestamps();
PHP,
    'shifts' => <<<PHP
            \$table->id();
            \$table->string('name', 100);
            \$table->string('code', 20)->unique();
            \$table->time('start_time');
            \$table->time('end_time');
            \$table->integer('late_after_minutes')->default(15);
            \$table->integer('early_leave_before_minutes')->default(15);
            \$table->integer('ot_after_minutes')->default(0);
            \$table->integer('break_duration_minutes')->default(60);
            \$table->json('working_days');
            \$table->boolean('is_active')->default(true);
            \$table->timestamps();
PHP,
    'employees' => <<<PHP
            \$table->id();
            \$table->foreignId('user_id')->constrained()->cascadeOnDelete();
            \$table->string('employee_code', 20)->unique();
            \$table->string('first_name', 100);
            \$table->string('last_name', 100);
            \$table->string('phone', 20)->nullable();
            \$table->foreignId('department_id')->constrained();
            \$table->foreignId('position_id')->constrained();
            \$table->foreignId('branch_id')->constrained();
            \$table->foreignId('supervisor_id')->nullable()->constrained('employees')->nullOnDelete();
            \$table->foreignId('shift_id')->constrained();
            \$table->enum('employment_status', ['active', 'probation', 'suspended', 'resigned', 'terminated'])->default('active');
            \$table->enum('check_in_mode', ['geofence', 'free', 'multi_site'])->default('geofence');
            \$table->string('profile_photo', 500)->nullable();
            \$table->date('hire_date')->nullable();
            \$table->timestamps();
            \$table->softDeletes();
PHP,
    'worksites' => <<<PHP
            \$table->id();
            \$table->string('name', 200);
            \$table->string('code', 20)->unique();
            \$table->text('address')->nullable();
            \$table->decimal('latitude', 10, 8);
            \$table->decimal('longitude', 11, 8);
            \$table->integer('radius_meters')->default(200);
            \$table->enum('geofence_type', ['circle', 'polygon'])->default('circle');
            \$table->json('polygon_coordinates')->nullable();
            \$table->boolean('is_active')->default(true);
            \$table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            \$table->timestamps();
            \$table->softDeletes();
PHP,
    'worksite_employee' => <<<PHP
            \$table->id();
            \$table->foreignId('worksite_id')->constrained()->cascadeOnDelete();
            \$table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            \$table->date('assigned_from')->nullable();
            \$table->date('assigned_to')->nullable();
            \$table->timestamps();
            \$table->unique(['worksite_id', 'employee_id']);
PHP,
    'attendances' => <<<PHP
            \$table->id();
            \$table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            \$table->date('date');
            \$table->foreignId('shift_id')->constrained();
            
            \$table->timestamp('check_in_at')->nullable();
            \$table->decimal('check_in_latitude', 10, 8)->nullable();
            \$table->decimal('check_in_longitude', 11, 8)->nullable();
            \$table->decimal('check_in_accuracy', 8, 2)->nullable();
            \$table->string('check_in_address', 500)->nullable();
            \$table->string('check_in_photo', 500)->nullable();
            \$table->text('check_in_note')->nullable();
            \$table->foreignId('check_in_worksite_id')->nullable()->constrained('worksites')->nullOnDelete();
            \$table->boolean('check_in_within_geofence')->nullable();
            \$table->text('check_in_outside_reason')->nullable();
            
            \$table->timestamp('check_out_at')->nullable();
            \$table->decimal('check_out_latitude', 10, 8)->nullable();
            \$table->decimal('check_out_longitude', 11, 8)->nullable();
            \$table->decimal('check_out_accuracy', 8, 2)->nullable();
            \$table->string('check_out_address', 500)->nullable();
            \$table->string('check_out_photo', 500)->nullable();
            \$table->text('check_out_note')->nullable();
            \$table->foreignId('check_out_worksite_id')->nullable()->constrained('worksites')->nullOnDelete();
            \$table->boolean('check_out_within_geofence')->nullable();
            \$table->text('check_out_outside_reason')->nullable();
            
            \$table->enum('status', ['checked_in', 'checked_out', 'missed_checkout', 'absent', 'leave', 'holiday'])->default('checked_in');
            \$table->boolean('is_late')->default(false);
            \$table->integer('late_minutes')->default(0);
            \$table->boolean('is_early_leave')->default(false);
            \$table->integer('early_leave_minutes')->default(0);
            \$table->integer('working_minutes')->default(0);
            \$table->integer('ot_minutes')->default(0);
            \$table->boolean('is_outside_geofence')->default(false);
            
            \$table->string('device_info', 500)->nullable();
            \$table->string('ip_address', 45)->nullable();
            \$table->boolean('is_amended')->default(false);
            
            \$table->timestamps();
            
            \$table->unique(['employee_id', 'date']);
PHP,
    'attendance_activities' => <<<PHP
            \$table->id();
            \$table->foreignId('attendance_id')->constrained()->cascadeOnDelete();
            \$table->enum('activity_type', ['site_arrival', 'site_departure', 'break_start', 'break_end', 'task_start', 'task_end']);
            \$table->timestamp('timestamp');
            \$table->decimal('latitude', 10, 8)->nullable();
            \$table->decimal('longitude', 11, 8)->nullable();
            \$table->decimal('accuracy', 8, 2)->nullable();
            \$table->string('photo', 500)->nullable();
            \$table->text('note')->nullable();
            \$table->foreignId('worksite_id')->nullable()->constrained()->nullOnDelete();
            \$table->timestamps();
PHP,
    'assignments' => <<<PHP
            \$table->id();
            \$table->string('title', 200);
            \$table->text('description')->nullable();
            \$table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            \$table->foreignId('assigned_by')->constrained('employees');
            \$table->foreignId('worksite_id')->nullable()->constrained()->nullOnDelete();
            \$table->date('scheduled_date');
            \$table->time('scheduled_time')->nullable();
            \$table->date('due_date')->nullable();
            \$table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            \$table->enum('status', ['assigned', 'accepted', 'in_progress', 'completed', 'cancelled'])->default('assigned');
            \$table->text('completion_note')->nullable();
            \$table->string('before_photo', 500)->nullable();
            \$table->string('after_photo', 500)->nullable();
            \$table->timestamp('completed_at')->nullable();
            \$table->timestamps();
            \$table->softDeletes();
PHP,
    'leave_types' => <<<PHP
            \$table->id();
            \$table->string('name', 100);
            \$table->string('code', 20)->unique();
            \$table->integer('default_days_per_year')->default(0);
            \$table->boolean('is_paid')->default(true);
            \$table->boolean('is_active')->default(true);
            \$table->timestamps();
PHP,
    'leave_requests' => <<<PHP
            \$table->id();
            \$table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            \$table->foreignId('leave_type_id')->constrained();
            \$table->date('start_date');
            \$table->date('end_date');
            \$table->decimal('total_days', 4, 1);
            \$table->text('reason');
            \$table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            \$table->foreignId('approved_by')->nullable()->constrained('employees')->nullOnDelete();
            \$table->timestamp('approved_at')->nullable();
            \$table->text('approval_note')->nullable();
            \$table->timestamps();
PHP,
    'leave_balances' => <<<PHP
            \$table->id();
            \$table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            \$table->foreignId('leave_type_id')->constrained();
            \$table->year('year');
            \$table->decimal('entitled_days', 4, 1);
            \$table->decimal('used_days', 4, 1)->default(0);
            \$table->decimal('remaining_days', 4, 1);
            \$table->timestamps();
            \$table->unique(['employee_id', 'leave_type_id', 'year'], 'leave_balances_unique');
PHP,
    'overtime_requests' => <<<PHP
            \$table->id();
            \$table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            \$table->date('date');
            \$table->time('planned_start');
            \$table->time('planned_end');
            \$table->decimal('planned_hours', 4, 1);
            \$table->text('reason');
            \$table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            \$table->foreignId('approved_by')->nullable()->constrained('employees')->nullOnDelete();
            \$table->timestamp('approved_at')->nullable();
            \$table->text('approval_note')->nullable();
            \$table->decimal('actual_hours', 4, 1)->nullable();
            \$table->timestamps();
PHP,
    'time_amendments' => <<<PHP
            \$table->id();
            \$table->foreignId('attendance_id')->constrained()->cascadeOnDelete();
            \$table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            \$table->enum('amendment_type', ['check_in', 'check_out', 'both']);
            \$table->timestamp('original_check_in')->nullable();
            \$table->timestamp('original_check_out')->nullable();
            \$table->timestamp('requested_check_in')->nullable();
            \$table->timestamp('requested_check_out')->nullable();
            \$table->text('reason');
            \$table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            \$table->foreignId('approved_by')->nullable()->constrained('employees')->nullOnDelete();
            \$table->timestamp('approved_at')->nullable();
            \$table->text('approval_note')->nullable();
            \$table->timestamps();
PHP,
    'holidays' => <<<PHP
            \$table->id();
            \$table->string('name', 200);
            \$table->date('date')->unique();
            \$table->boolean('is_recurring')->default(false);
            \$table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            \$table->timestamps();
PHP,
    'settings' => <<<PHP
            \$table->id();
            \$table->string('key', 100)->unique();
            \$table->text('value')->nullable();
            \$table->string('group', 50)->default('general');
            \$table->timestamps();
PHP,
    'audit_logs' => <<<PHP
            \$table->id();
            \$table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            \$table->string('action', 50);
            \$table->string('auditable_type');
            \$table->unsignedBigInteger('auditable_id');
            \$table->json('old_values')->nullable();
            \$table->json('new_values')->nullable();
            \$table->string('ip_address', 45)->nullable();
            \$table->string('user_agent', 500)->nullable();
            \$table->timestamps();
            
            \$table->index(['auditable_type', 'auditable_id']);
PHP,
];

foreach ($files as $file) {
    if (!str_ends_with($file, '.php')) continue;
    
    // extract table name
    preg_match('/_create_(.*)_table\.php/', $file, $matches);
    if (empty($matches)) continue;
    $table = $matches[1];
    
    if (isset($schemas[$table])) {
        $content = file_get_contents($dir . $file);
        
        $pattern = '/Schema::create\(\'' . $table . '\', function \(Blueprint \$table\) \{(.*?)\}\);/s';
        $replacement = "Schema::create('$table', function (Blueprint \$table) {\n" . $schemas[$table] . "\n        });";
        
        $newContent = preg_replace($pattern, $replacement, $content);
        file_put_contents($dir . $file, $newContent);
        echo "Updated $file\n";
    }
}
