<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Department;
use App\Models\Position;
use App\Models\Branch;
use App\Models\Shift;
use App\Models\Employee;
use App\Models\Worksite;
use App\Enums\UserRole;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Branches
        $headOffice = Branch::firstOrCreate(['code' => 'HQ'], ['name' => 'สำนักงานใหญ่', 'latitude' => 13.7563, 'longitude' => 100.5018]);
        
        // Departments
        $hr = Department::firstOrCreate(['code' => 'HR'], ['name' => 'ทรัพยากรบุคคล']);
        $it = Department::firstOrCreate(['code' => 'IT'], ['name' => 'เทคโนโลยีสารสนเทศ']);
        $field = Department::firstOrCreate(['code' => 'FIELD'], ['name' => 'ช่างติดตั้งและบริการ']);

        // Positions
        $hrManager = Position::firstOrCreate(['name' => 'HR Manager']);
        $itAdmin = Position::firstOrCreate(['name' => 'IT Admin']);
        $supervisor = Position::firstOrCreate(['name' => 'Field Supervisor']);
        $technician = Position::firstOrCreate(['name' => 'Technician']);

        // Shifts
        $normalShift = Shift::firstOrCreate(['code' => 'S1'], [
            'name' => 'กะปกติ',
            'start_time' => '08:30:00',
            'end_time' => '17:30:00',
            'working_days' => '[1,2,3,4,5]',
        ]);

        // Admin User
        $adminUser = User::firstOrCreate(['email' => 'admin@cfarm.co.th'], [
            'username' => 'admin',
            'password' => Hash::make('password'),
            'role' => UserRole::ADMIN,
        ]);

        // HR User
        $hrUser = User::firstOrCreate(['email' => 'hr@cfarm.co.th'], [
            'username' => 'hr_manager',
            'password' => Hash::make('password'),
            'role' => UserRole::HR,
        ]);

        // Supervisor User
        $supUser = User::firstOrCreate(['email' => 'sup01@cfarm.co.th'], [
            'username' => 'supervisor_01',
            'password' => Hash::make('password'),
            'role' => UserRole::SUPERVISOR,
        ]);

        // Employee User
        $empUser = User::firstOrCreate(['email' => 'tech01@cfarm.co.th'], [
            'username' => 'tech_01',
            'password' => Hash::make('password'),
            'role' => UserRole::EMPLOYEE,
        ]);

        // Create Employees
        $hrEmp = Employee::firstOrCreate(['employee_code' => 'EMP001'], [
            'user_id' => $hrUser->id,
            'first_name' => 'สมศรี',
            'last_name' => 'ใจดี',
            'department_id' => $hr->id,
            'position_id' => $hrManager->id,
            'branch_id' => $headOffice->id,
            'shift_id' => $normalShift->id,
            'check_in_mode' => 'free',
        ]);

        $supEmp = Employee::firstOrCreate(['employee_code' => 'EMP002'], [
            'user_id' => $supUser->id,
            'first_name' => 'สมชาย',
            'last_name' => 'สายช่าง',
            'department_id' => $field->id,
            'position_id' => $supervisor->id,
            'branch_id' => $headOffice->id,
            'shift_id' => $normalShift->id,
            'check_in_mode' => 'geofence',
        ]);

        $techEmp = Employee::firstOrCreate(['employee_code' => 'EMP003'], [
            'user_id' => $empUser->id,
            'first_name' => 'สมเกียรติ',
            'last_name' => 'ช่างไฟ',
            'department_id' => $field->id,
            'position_id' => $technician->id,
            'branch_id' => $headOffice->id,
            'supervisor_id' => $supEmp->id,
            'shift_id' => $normalShift->id,
            'check_in_mode' => 'geofence',
        ]);

        // Worksites
        $site = Worksite::firstOrCreate(['code' => 'SITE-A'], [
            'name' => 'ไซต์ก่อสร้างคอนโด A',
            'address' => 'สุขุมวิท 101',
            'latitude' => 13.6946, 
            'longitude' => 100.6094,
            'radius_meters' => 300,
        ]);

        // Assign site to tech
        if (!$techEmp->worksites()->where('worksite_id', $site->id)->exists()) {
            $techEmp->worksites()->attach($site->id);
        }
    }
}
