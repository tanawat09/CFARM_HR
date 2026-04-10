<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\LeaveRequest;
use App\Models\LeavePolicy;
use App\Models\Employee;
use App\Enums\LeaveType;
use App\Enums\LeaveStatus;
use App\Enums\EmploymentStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class LeaveController extends Controller
{
    /**
     * Calculate effective quota for a policy given an employee.
     */
    private function effectiveQuota(LeavePolicy $policy, ?Employee $employee): int
    {
        $base = $policy->quota_days;

        if (!$employee || !$employee->hire_date) return $base;

        if ($policy->is_tenure_based && $policy->tenure_threshold_years && $policy->tenure_bonus_days) {
            $years = Carbon::parse($employee->hire_date)->diffInYears(Carbon::now());
            if ($years >= $policy->tenure_threshold_years) {
                return $policy->tenure_bonus_days;
            }
        }

        return $base;
    }

    /**
     * Build complete quota stats for an employee using leave_policies table.
     */
    private function buildStats(?Employee $employee): array
    {
        $policies = LeavePolicy::where('is_active', true)->orderBy('sort_order')->get();
        $year = Carbon::now()->year;
        $isProbation = $employee ? ($employee->employment_status === EmploymentStatus::PROBATION) : false;

        $items = [];
        foreach ($policies as $p) {
            $quota = $this->effectiveQuota($p, $employee);
            $used = 0;
            if ($employee) {
                $used = (float) LeaveRequest::where('employee_id', $employee->id)
                    ->where('leave_type', $p->key)
                    ->where('status', LeaveStatus::APPROVED->value)
                    ->whereYear('start_date', $year)
                    ->sum('total_days');
            }
            $items[$p->key] = [
                'key'       => $p->key,
                'name'      => $p->name,
                'icon'      => $p->icon,
                'quota'     => $quota,
                'used'      => $used,
                'remaining' => $quota - $used,
                'requires_reason' => $p->requires_reason,
                'requires_attachment_after_days' => $p->requires_attachment_after_days,
                'probation_allowed' => $p->probation_allowed,
            ];
        }

        return [
            'items'        => $items,
            'is_probation' => $isProbation,
            'hire_date'    => $employee?->hire_date?->toDateString(),
        ];
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $employee = $user->employee;

        $query = LeaveRequest::query();
        if ($employee) {
            $query->where('employee_id', $employee->id);
        } else {
            $query->where('id', 0);
        }
        $query->orderBy('created_at', 'desc');
        $leaves = $query->paginate(10);

        $policies = LeavePolicy::where('is_active', true)->orderBy('sort_order')->get();

        return Inertia::render('Leave/Index', [
            'leaves'     => $leaves,
            'stats'      => $this->buildStats($employee),
            'leaveTypes' => $policies->map(fn($p) => [
                'key' => $p->key, 'name' => $p->name, 'icon' => $p->icon,
            ])->values(),
        ]);
    }

    public function create(Request $request)
    {
        $employee = $request->user()->employee;
        $policies = LeavePolicy::where('is_active', true)->orderBy('sort_order')->get();

        return Inertia::render('Leave/Create', [
            'leaveTypes' => $policies->map(fn($p) => [
                'key' => $p->key, 'name' => $p->name, 'icon' => $p->icon,
            ])->values(),
            'stats' => $this->buildStats($employee),
        ]);
    }

    public function store(Request $request)
    {
        $employee = $request->user()->employee;
        if (!$employee) {
            return redirect()->back()->withErrors(['leave_type' => 'บัญชีของคุณไม่มีข้อมูลพนักงาน ไม่สามารถยื่นใบลาได้']);
        }

        $leaveTypeKey = $request->input('leave_type');
        $policy = LeavePolicy::where('key', $leaveTypeKey)->where('is_active', true)->first();

        if (!$policy) {
            return redirect()->back()->withErrors(['leave_type' => 'ประเภทการลาไม่ถูกต้องหรือถูกปิดใช้งาน']);
        }

        // --- Dynamic validation rules ---
        $rules = [
            'leave_type'  => 'required|string|exists:leave_policies,key',
            'start_date'  => 'required|date|after_or_equal:today',
            'end_date'    => 'required|date|after_or_equal:start_date',
        ];

        // Reason
        $rules['reason'] = $policy->requires_reason ? 'required|string|max:1000' : 'nullable|string|max:1000';

        // Total days
        $totalDays = 0;
        if ($request->start_date && $request->end_date) {
            try {
                $totalDays = Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date)) + 1;
            } catch (\Exception $e) {
                $totalDays = 0;
            }
        }

        // Attachment
        $threshold = $policy->requires_attachment_after_days;
        if ($threshold && $totalDays >= $threshold) {
            $rules['attachment'] = 'required|file|mimes:jpg,jpeg,png,pdf|max:5120';
        } else {
            $rules['attachment'] = 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120';
        }

        $validated = $request->validate($rules, [
            'attachment.required' => "ลา{$policy->name}ตั้งแต่ {$threshold} วันขึ้นไป จำเป็นต้องแนบเอกสาร",
            'reason.required' => 'กรุณาระบุเหตุผลในการลา',
        ]);

        // --- Probation check ---
        $isProbation = $employee->employment_status === EmploymentStatus::PROBATION;
        if ($isProbation && !$policy->probation_allowed) {
            return redirect()->back()->withErrors(['leave_type' => "พนักงานทดลองงานไม่สามารถ{$policy->name}ได้"]);
        }

        // --- Quota check ---
        $quota = $this->effectiveQuota($policy, $employee);
        $year = Carbon::now()->year;
        $used = (float) LeaveRequest::where('employee_id', $employee->id)
            ->where('leave_type', $policy->key)
            ->where('status', LeaveStatus::APPROVED->value)
            ->whereYear('start_date', $year)
            ->sum('total_days');
        $remaining = $quota - $used;

        if ($totalDays > $remaining) {
            return redirect()->back()->withErrors(['leave_type' => "สิทธิ์{$policy->name}คงเหลือไม่เพียงพอ (เหลือ {$remaining} วัน, คุณขอ {$totalDays} วัน)"]);
        }

        // Handle file upload
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('leave-attachments', 'public');
        }

        LeaveRequest::create([
            'employee_id'     => $employee->id,
            'leave_type'      => $validated['leave_type'],
            'start_date'      => $validated['start_date'],
            'end_date'        => $validated['end_date'],
            'total_days'      => $totalDays,
            'reason'          => $validated['reason'] ?? null,
            'status'          => LeaveStatus::PENDING->value,
            'attachment_path' => $attachmentPath,
        ]);

        return redirect()->route('leave.index')->with('message', 'ส่งคำขอลาเรียบร้อยแล้ว');
    }
}
