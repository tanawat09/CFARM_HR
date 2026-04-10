<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use App\Enums\AttendanceStatus;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if ($user->role->value === 'admin' || $user->role->value === 'hr') {
            $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::now()->format('Y-m-d');

            // Basic Stats
            $totalEmployees = Employee::count();
            $totalCheckInsToday = Attendance::where('date', Carbon::today()->format('Y-m-d'))
                ->whereIn('status', [AttendanceStatus::CHECKED_IN, AttendanceStatus::CHECKED_OUT])
                ->count();
            
            $totalLatesMonth = Attendance::whereBetween('date', [$startDate, $endDate])
                ->where('is_late', true)
                ->count();
                
            $totalLeavesMonth = \App\Models\LeaveRequest::where('status', 'approved')
                ->where(function($query) use ($startDate, $endDate) {
                    $query->whereBetween('start_date', [$startDate, $endDate])
                        ->orWhereBetween('end_date', [$startDate, $endDate]);
                })->count();

            // Daily Attendance Chart Data (Last 7 Days)
            $chartData = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i)->format('Y-m-d');
                $dayLabel = Carbon::now()->subDays($i)->format('D');
                
                $onTime = Attendance::where('date', $date)->where('is_late', false)->count();
                $late = Attendance::where('date', $date)->where('is_late', true)->count();
                
                $chartData[] = [
                    'date' => $date,
                    'label' => $dayLabel,
                    'on_time' => $onTime,
                    'late' => $late,
                    // MVP Faked data for visual impact if empty
                    'fake_on_time' => $onTime > 0 ? $onTime : rand((int)($totalEmployees * 0.6), (int)($totalEmployees * 0.8)),
                    'fake_late' => $late > 0 ? $late : rand(0, (int)($totalEmployees * 0.15))
                ];
            }

            return Inertia::render('Dashboard/Admin', [
                'stats' => [
                    'total_employees' => $totalEmployees > 0 ? $totalEmployees : 25,
                    'checked_in_today' => $totalCheckInsToday,
                    'total_lates_month' => $totalLatesMonth,
                    'total_leaves_month' => $totalLeavesMonth,
                    'avg_attendance_rate' => $totalEmployees > 0 ? round(($totalCheckInsToday / $totalEmployees) * 100, 1) : 0,
                    'pending_leaves' => \App\Models\LeaveRequest::where('status', 'pending')->count(),
                ],
                'chartData' => $chartData,
                'recentLeaves' => \App\Models\LeaveRequest::with('employee')
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get()
                    ->map(fn($l) => [
                        'id' => $l->id,
                        'employee_name' => $l->employee?->first_name . ' ' . $l->employee?->last_name,
                        'leave_type' => $l->leave_type?->value ?? $l->leave_type,
                        'start_date' => $l->start_date?->format('Y-m-d'),
                        'end_date' => $l->end_date?->format('Y-m-d'),
                        'total_days' => $l->total_days,
                        'status' => $l->status?->value ?? $l->status,
                    ]),
                'todayAttendance' => Attendance::with(['employee.department', 'employee.position', 'employee.shift'])
                    ->where('date', Carbon::today()->format('Y-m-d'))
                    ->orderBy('check_in_at', 'desc')
                    ->limit(10)
                    ->get()
                    ->map(fn($a) => [
                        'id' => $a->id,
                        'employee_name' => $a->employee?->first_name . ' ' . $a->employee?->last_name,
                        'department' => $a->employee?->department?->name,
                        'shift_name' => $a->employee?->shift?->name,
                        'shift_time' => $a->employee?->shift ? (substr($a->employee->shift->start_time, 0, 5) . '-' . substr($a->employee->shift->end_time, 0, 5)) : null,
                        'check_in_time' => $a->check_in_at?->format('H:i'),
                        'check_out_time' => $a->check_out_at?->format('H:i'),
                        'status' => $a->status?->value ?? $a->status,
                        'is_late' => $a->is_late,
                    ]),
                'departments' => \App\Models\Department::withCount('employees')->get()->map(fn($d) => [
                    'name' => $d->name,
                    'count' => $d->employees_count,
                ]),
                'policies' => \App\Models\LeavePolicy::where('is_active', true)->get()->keyBy('key'),
            ]);
        }

        if ($user->role->value === 'supervisor') {
            $subordinates = $employee ? $employee->subordinates()->with(['department', 'position', 'user', 'shift'])->get() : collect([]);
            $subordinateIds = $subordinates->pluck('id');

            $today = Carbon::today()->format('Y-m-d');
            $startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');

            // Today's attendance for team
            $todayAttendances = \App\Models\Attendance::whereIn('employee_id', $subordinateIds)
                ->where('date', $today)
                ->get()
                ->keyBy('employee_id');

            // Team members with today's status
            $teamMembers = $subordinates->map(function ($sub) use ($todayAttendances) {
                $att = $todayAttendances->get($sub->id);
                return [
                    'id' => $sub->id,
                    'first_name' => $sub->first_name,
                    'last_name' => $sub->last_name,
                    'department' => $sub->department?->name,
                    'position' => $sub->position?->name,
                    'shift_name' => $sub->shift?->name,
                    'shift_time' => $sub->shift ? (substr($sub->shift->start_time, 0, 5) . '-' . substr($sub->shift->end_time, 0, 5)) : null,
                    'today_status' => $att ? ($att->status?->value ?? $att->status) : 'absent',
                    'check_in_time' => $att?->check_in_at?->format('H:i'),
                    'check_out_time' => $att?->check_out_at?->format('H:i'),
                    'is_late' => $att?->is_late ?? false,
                ];
            });

            // Pending leaves count
            $pendingLeaves = \App\Models\LeaveRequest::whereIn('employee_id', $subordinateIds)
                ->where('status', \App\Enums\LeaveStatus::PENDING->value)
                ->count();

            // Recent leave requests
            $recentLeaves = \App\Models\LeaveRequest::with('employee')
                ->whereIn('employee_id', $subordinateIds)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(fn($l) => [
                    'id' => $l->id,
                    'employee_name' => $l->employee?->first_name . ' ' . $l->employee?->last_name,
                    'leave_type' => $l->leave_type?->value ?? $l->leave_type,
                    'start_date' => $l->start_date?->format('Y-m-d'),
                    'end_date' => $l->end_date?->format('Y-m-d'),
                    'total_days' => $l->total_days,
                    'status' => $l->status?->value ?? $l->status,
                ]);

            // Stats
            $checkedInToday = $todayAttendances->count();
            $lateToday = $todayAttendances->where('is_late', true)->count();
            $totalTeam = $subordinates->count();

            // Monthly attendance for the team
            $monthlyAttendance = \App\Models\Attendance::whereIn('employee_id', $subordinateIds)
                ->whereBetween('date', [$startOfMonth, $today])
                ->count();

            // Leave policies for labels
            $policies = \App\Models\LeavePolicy::where('is_active', true)->get()->keyBy('key');

            // My own attendance today
            $myAttendance = \App\Models\Attendance::where('employee_id', $employee->id)
                ->where('date', $today)
                ->first();

            return Inertia::render('Dashboard/Supervisor', [
                'employee' => $employee->load(['department', 'position']),
                'teamMembers' => $teamMembers,
                'pendingLeaves' => $pendingLeaves,
                'recentLeaves' => $recentLeaves,
                'policies' => $policies,
                'myAttendance' => $myAttendance,
                'stats' => [
                    'total_team' => $totalTeam,
                    'checked_in_today' => $checkedInToday,
                    'late_today' => $lateToday,
                    'absent_today' => $totalTeam - $checkedInToday,
                    'monthly_attendance' => $monthlyAttendance,
                ],
            ]);
        }

        // Employee Dashboard
        $todayAttendance = null;
        if ($employee) {
            $todayAttendance = \App\Models\Attendance::where('employee_id', $employee->id)
                ->where('date', Carbon::today()->format('Y-m-d'))
                ->first();
        }

        return Inertia::render('Dashboard/Employee', [
            'employee' => $employee ? $employee->load(['department', 'position', 'shift']) : null,
            'today_attendance' => $todayAttendance,
            'check_in_mode' => $employee ? $employee->check_in_mode : null,
            'worksites' => $employee ? $employee->worksites : [],
        ]);
    }
}
