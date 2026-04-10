<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $monthParam = $request->input('month', Carbon::now()->format('Y-m'));
        $departmentId = $request->input('department_id');
        $search = $request->input('search');

        try {
            $date = Carbon::createFromFormat('Y-m', $monthParam);
        } catch (\Exception $e) {
            $date = Carbon::now();
            $monthParam = $date->format('Y-m');
        }
        
        $startDate = $date->copy()->startOfMonth();
        $endDate = $date->copy()->endOfMonth();

        // 1. Employee Context Filter (Calculate based on who is "working")
        $employeeQuery = Employee::query()
            ->whereIn('employment_status', [\App\Enums\EmploymentStatus::ACTIVE->value, \App\Enums\EmploymentStatus::PROBATION->value]);
        
        if ($departmentId) {
            $employeeQuery->where('department_id', $departmentId);
        }
        
        if ($search) {
            $employeeQuery->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        $totalEmployees = (clone $employeeQuery)->count();
        $employeeIds = (clone $employeeQuery)->pluck('id');

        // 2. Attendance Stats for this group
        $attendanceQuery = Attendance::whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->whereIn('employee_id', $employeeIds);

        $totalCheckIns = (clone $attendanceQuery)->count();
        $totalLates = (clone $attendanceQuery)->where('is_late', true)->count();
        
        $totalLeaves = LeaveRequest::where('status', 'approved')
            ->whereIn('employee_id', $employeeIds)
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                      ->orWhereBetween('end_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
            })->count();

        // 3. Avg Attendance Rate Calculation
        $daysInMonth = $date->daysInMonth;
        $today = Carbon::today();
        $daysToCount = ($date->month == $today->month && $date->year == $today->year) 
            ? min($daysInMonth, $today->day) 
            : $daysInMonth;
        
        // Weekend subtraction could be better but let's stick to total days for simple overview
        $avgAttendanceRate = ($totalEmployees > 0 && $daysToCount > 0)
            ? round(($totalCheckIns / ($totalEmployees * $daysToCount)) * 100, 1)
            : 0;

        // 4. Daily Data for Chart
        $dailyData = (clone $attendanceQuery)
                        ->select('date', DB::raw('count(*) as total'), DB::raw('sum(is_late = true) as late_count'))
                        ->groupBy('date')
                        ->orderBy('date')
                        ->get()
                        ->keyBy('date');

        $chartData = [];
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $currentDateStr = $date->copy()->day($i)->format('Y-m-d');
            $dayLabel = $date->copy()->day($i)->day; // Just day number for crowded charts
            
            $on_time = 0;
            $late = 0;
            
            if ($dailyData->has($currentDateStr)) {
                $late = (int)$dailyData[$currentDateStr]->late_count;
                $on_time = (int)$dailyData[$currentDateStr]->total - $late;
            }
            
            $chartData[] = [
                'date' => $currentDateStr,
                'label' => $dayLabel,
                'on_time' => $on_time,
                'late' => $late,
            ];
        }

        // 5. Paginated List
        $attendances = (clone $attendanceQuery)
            ->with(['employee.department', 'employee.position', 'shift', 'checkInWorksite'])
            ->orderBy('date', 'desc')
            ->orderBy('check_in_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $attendances->getCollection()->transform(function($a) {
            return [
                'id' => $a->id,
                'date' => $a->date->format('Y-m-d'),
                'employee_name' => $a->employee ? $a->employee->first_name . ' ' . $a->employee->last_name : 'Unknown',
                'department' => $a->employee?->department?->name ?? '-',
                'shift_name' => $a->shift?->name,
                'check_in_at' => $a->check_in_at?->format('H:i'),
                'check_out_at' => $a->check_out_at?->format('H:i'),
                'status' => $a->status?->value ?? $a->status,
                'is_late' => $a->is_late,
                'late_minutes' => $a->late_minutes,
                'worksite' => $a->checkInWorksite?->name ?? 'ไม่ระบุ'
            ];
        });

        return Inertia::render('Report/Index', [
            'stats' => [
                'total_employees' => $totalEmployees,
                'avg_attendance_rate' => min(100, $avgAttendanceRate),
                'total_lates' => $totalLates,
                'total_leaves' => $totalLeaves
            ],
            'chartData' => $chartData,
            'attendances' => $attendances,
            'departments' => \App\Models\Department::select('id', 'name')->get(),
            'filters' => [
                'month' => $monthParam,
                'department_id' => $departmentId,
                'search' => $search,
            ]
        ]);
    }

    public function export(Request $request)
    {
        $monthParam = $request->input('month', Carbon::now()->format('Y-m'));
        $departmentId = $request->input('department_id');
        $search = $request->input('search');

        try {
            $date = Carbon::createFromFormat('Y-m', $monthParam);
        } catch (\Exception $e) {
            $date = Carbon::now();
        }
        
        $startDate = $date->copy()->startOfMonth()->format('Y-m-d');
        $endDate = $date->copy()->endOfMonth()->format('Y-m-d');

        $query = Attendance::with(['employee.department', 'employee.position', 'shift', 'checkInWorksite'])
            ->whereBetween('date', [$startDate, $endDate]);

        // Apply filters
        if ($departmentId || $search) {
            $query->whereHas('employee', function($q) use ($departmentId, $search) {
                if ($departmentId) {
                    $q->where('department_id', $departmentId);
                }
                if ($search) {
                    $q->where(function($sq) use ($search) {
                        $sq->where('first_name', 'like', "%{$search}%")
                          ->orWhere('last_name', 'like', "%{$search}%")
                          ->orWhere('employee_code', 'like', "%{$search}%");
                    });
                }
            });
        }

        $attendances = $query->orderBy('date', 'desc')
            ->orderBy('check_in_at', 'desc')
            ->get();

        $filename = "attendance_report_{$monthParam}";
        if ($departmentId) $filename .= "_dept_{$departmentId}";
        $filename .= ".csv";

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($attendances) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, ['วันที่', 'พนักงาน', 'แผนก', 'ตำแหน่ง', 'กะ', 'สถานที่เข้างาน', 'เวลาเข้า', 'เวลาออก', 'สาย (นาที)', 'สถานะ']);

            foreach ($attendances as $a) {
                fputcsv($file, [
                    $a->date->format('d/m/Y'),
                    $a->employee ? $a->employee->first_name . ' ' . $a->employee->last_name : '-',
                    $a->employee?->department?->name ?? '-',
                    $a->employee?->position?->name ?? '-',
                    $a->shift?->name ?? '-',
                    $a->checkInWorksite?->name ?? '-',
                    $a->check_in_at?->format('H:i') ?? '-',
                    $a->check_out_at?->format('H:i') ?? '-',
                    $a->is_late ? $a->late_minutes : 0,
                    $a->status?->value ?? $a->status
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function leaves(Request $request)
    {
        $monthParam = $request->input('month', Carbon::now()->format('Y-m'));
        try {
            $date = Carbon::createFromFormat('Y-m', $monthParam);
        } catch (\Exception $e) {
            $date = Carbon::now();
            $monthParam = $date->format('Y-m');
        }
        
        $startDate = $date->copy()->startOfMonth()->format('Y-m-d');
        $endDate = $date->copy()->endOfMonth()->format('Y-m-d');

        // Stats
        $query = LeaveRequest::where(function($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
              ->orWhereBetween('end_date', [$startDate, $endDate]);
        });

        $totalApproved = (clone $query)->where('status', 'approved')->count();
        $totalPending = (clone $query)->where('status', 'pending')->count();
        $totalRejected = (clone $query)->where('status', 'rejected')->count();

        // Chart Data (Leaves by Type)
        $leaveTypes = (clone $query)->where('status', 'approved')
            ->select('leave_type', DB::raw('count(*) as count'))
            ->groupBy('leave_type')
            ->get()
            ->map(function($item) {
                return [
                    'type' => $item->leave_type->value ?? $item->leave_type,
                    'count' => $item->count
                ];
            });

        // Table Data
        $leaves = LeaveRequest::with(['employee.department', 'employee.position', 'approver'])
            ->where(function($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate]);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $leaves->getCollection()->transform(function($l) {
            return [
                'id' => $l->id,
                'employee_name' => $l->employee ? $l->employee->first_name . ' ' . $l->employee->last_name : 'Unknown',
                'department' => $l->employee?->department?->name ?? '-',
                'leave_type' => $l->leave_type?->value ?? $l->leave_type,
                'start_date' => $l->start_date?->format('Y-m-d'),
                'end_date' => $l->end_date?->format('Y-m-d'),
                'total_days' => floatval($l->total_days),
                'status' => $l->status?->value ?? $l->status,
                'reason' => $l->reason,
                'approver_name' => $l->approver ? $l->approver->first_name : '-'
            ];
        });

        return Inertia::render('Report/Leaves', [
            'stats' => [
                'approved' => $totalApproved,
                'pending' => $totalPending,
                'rejected' => $totalRejected
            ],
            'chartData' => $leaveTypes,
            'leaves' => $leaves,
            'filters' => [
                'month' => $monthParam,
            ]
        ]);
    }

    public function exportLeaves(Request $request)
    {
        $monthParam = $request->input('month', Carbon::now()->format('Y-m'));
        try {
            $date = Carbon::createFromFormat('Y-m', $monthParam);
        } catch (\Exception $e) {
            $date = Carbon::now();
        }
        
        $startDate = $date->copy()->startOfMonth()->format('Y-m-d');
        $endDate = $date->copy()->endOfMonth()->format('Y-m-d');

        $leaves = LeaveRequest::with(['employee.department', 'employee.position', 'approver'])
            ->where(function($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate]);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = "leave_report_{$monthParam}.csv";
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($leaves) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, ['วันที่ยื่นลา', 'พนักงาน', 'แผนก', 'ประเภทการลา', 'เริ่ม', 'สิ้นสุด', 'จำนวนวัน', 'สถานะ', 'ผู้อนุมัติ', 'เหตุผล']);

            foreach ($leaves as $l) {
                fputcsv($file, [
                    $l->created_at->format('d/m/Y'),
                    $l->employee ? $l->employee->first_name . ' ' . $l->employee->last_name : '-',
                    $l->employee?->department?->name ?? '-',
                    $l->leave_type?->value ?? $l->leave_type,
                    $l->start_date?->format('d/m/Y') ?? '-',
                    $l->end_date?->format('d/m/Y') ?? '-',
                    $l->total_days,
                    $l->status?->value ?? $l->status,
                    $l->approver ? $l->approver->first_name : '-',
                    $l->reason
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
