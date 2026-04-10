<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Position;
use App\Models\Worksite;
use App\Models\Shift;
use App\Models\User;
use App\Enums\UserRole;
use App\Enums\EmploymentStatus;
use App\Enums\CheckInMode;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with(['department', 'position', 'branch', 'user']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }
        
        // Filter by Department
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $employees = $query->paginate(15)->withQueryString();

        return Inertia::render('Employee/Index', [
            'employees' => $employees,
            'departments' => Department::all(),
            'filters' => $request->only(['search', 'department_id'])
        ]);
    }

    public function create()
    {
        return Inertia::render('Employee/Create', [
            'departments' => Department::all(),
            'positions' => Position::all(),
            'branches' => Worksite::all(),
            'shifts' => Shift::all(),
            'supervisors' => Employee::select('id', 'first_name', 'last_name')
                ->where(function($q) {
                    $q->whereHas('department', function($q_dep) {
                        $q_dep->where('name', 'บริหาร');
                    })->orWhereHas('position', function($q_pos) {
                        $q_pos->where('name', 'like', '%ผู้จัดการฝ่าย%');
                    });
                })->get(),
            'employmentStatuses' => array_column(EmploymentStatus::cases(), 'value'),
            'checkInModes' => array_column(CheckInMode::cases(), 'value')
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_code' => 'required|string|max:50|unique:employees,employee_code',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'department_id' => 'required|exists:departments,id',
            'position_id' => 'required|exists:positions,id',
            'branch_id' => 'nullable|exists:worksites,id',
            'shift_id' => 'required|exists:shifts,id',
            'supervisor_id' => 'nullable|exists:employees,id',
            'hire_date' => 'required|date',
        ]);

        // Create user
        $user = User::create([
            'username' => $validated['employee_code'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => UserRole::EMPLOYEE->value, // Default to employee role
            'is_active' => true,
        ]);

        // Create employee
        $employee = Employee::create([
            'user_id' => $user->id,
            'employee_code' => $validated['employee_code'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'department_id' => $validated['department_id'],
            'position_id' => $validated['position_id'],
            'branch_id' => $validated['branch_id'],
            'shift_id' => $validated['shift_id'],
            'supervisor_id' => $validated['supervisor_id'] ?? null,
            'employment_status' => EmploymentStatus::ACTIVE->value,
            'check_in_mode' => CheckInMode::GEOFENCE->value,
            'hire_date' => $validated['hire_date'],
        ]);

        // If a supervisor was assigned, ensure their user role is set to supervisor
        if (!empty($validated['supervisor_id'])) {
            $supervisor = Employee::find($validated['supervisor_id']);
            if ($supervisor && $supervisor->user) {
                $supervisor->user->update(['role' => UserRole::SUPERVISOR->value]);
            }
        }

        return redirect()->route('employees.index')->with('message', 'เพิ่มพนักงานสำเร็จแล้ว');
    }

    public function edit(Employee $employee)
    {
        $employee->load(['user', 'department', 'position', 'branch', 'shift']);

        return Inertia::render('Employee/Edit', [
            'employee'    => $employee,
            'departments' => Department::all(),
            'positions'   => Position::all(),
            'branches'    => Worksite::all(),
            'shifts'      => Shift::all(),
            'supervisors' => Employee::select('id', 'first_name', 'last_name')
                ->where('id', '!=', $employee->id)
                ->where(function($q) {
                    $q->whereHas('department', function($q_dep) {
                        $q_dep->where('name', 'บริหาร');
                    })->orWhereHas('position', function($q_pos) {
                        $q_pos->where('name', 'like', '%ผู้จัดการฝ่าย%');
                    });
                })->get(),
            'employmentStatuses' => array_map(fn($s) => ['value' => $s->value, 'label' => $s->label()], EmploymentStatus::cases()),
        ]);
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'first_name'         => 'required|string|max:100',
            'last_name'          => 'required|string|max:100',
            'phone'              => 'nullable|string|max:20',
            'department_id'      => 'required|exists:departments,id',
            'position_id'        => 'required|exists:positions,id',
            'branch_id'          => 'nullable|exists:worksites,id',
            'shift_id'           => 'required|exists:shifts,id',
            'supervisor_id'      => 'nullable|exists:employees,id',
            'hire_date'          => 'required|date',
            'employment_status'  => 'required|string',
            'password'           => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $employee->update($validated);

        // If a new supervisor was assigned, update their role to supervisor
        if (!empty($validated['supervisor_id'])) {
            $newSupervisor = Employee::find($validated['supervisor_id']);
            if ($newSupervisor && $newSupervisor->user) {
                $newSupervisor->user->update(['role' => UserRole::SUPERVISOR->value]);
            }
        }

        // Update password if provided
        if ($request->filled('password')) {
            $employee->user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        return redirect()->route('employees.index')->with('message', 'อัพเดทข้อมูลพนักงานเรียบร้อย');
    }
}
