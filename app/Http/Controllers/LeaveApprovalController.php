<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\LeaveRequest;
use App\Models\Employee;
use App\Models\LeavePolicy;
use App\Enums\LeaveStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class LeaveApprovalController extends Controller
{
    /**
     * Display leave requests pending approval.
     * - Admin/HR sees ALL pending requests.
     * - Supervisor sees only their direct subordinates' requests.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;
        $role = $user->role->value;

        $tab = $request->input('tab', 'pending');

        $query = LeaveRequest::with(['employee.department', 'employee.position', 'employee.supervisor', 'approver']);

        // Scope by role
        if (in_array($role, ['admin', 'hr'])) {
            // Admin/HR sees everything
        } elseif ($employee) {
            // Supervisor sees only direct subordinates
            $subordinateIds = Employee::where('supervisor_id', $employee->id)->pluck('id');
            $query->whereIn('employee_id', $subordinateIds);
        } else {
            $query->where('id', 0); // No access
        }

        // Tab filter
        if ($tab === 'pending') {
            $query->where('status', LeaveStatus::PENDING->value);
        } elseif ($tab === 'approved') {
            $query->where('status', LeaveStatus::APPROVED->value);
        } elseif ($tab === 'rejected') {
            $query->where('status', LeaveStatus::REJECTED->value);
        }

        $query->orderBy('created_at', 'desc');
        $leaves = $query->paginate(15)->withQueryString();

        // Count pending for badge
        $pendingQuery = LeaveRequest::where('status', LeaveStatus::PENDING->value);
        if (!in_array($role, ['admin', 'hr']) && $employee) {
            $subordinateIds = Employee::where('supervisor_id', $employee->id)->pluck('id');
            $pendingQuery->whereIn('employee_id', $subordinateIds);
        } elseif (!in_array($role, ['admin', 'hr'])) {
            $pendingQuery->where('id', 0);
        }
        $pendingCount = $pendingQuery->count();

        // Leave policies for type labels
        $policies = LeavePolicy::where('is_active', true)->get()->keyBy('key');

        return Inertia::render('Leave/Approvals', [
            'leaves'       => $leaves,
            'pendingCount' => $pendingCount,
            'policies'     => $policies,
            'filters'      => ['tab' => $tab],
        ]);
    }

    /**
     * Approve a leave request.
     */
    public function approve(Request $request, LeaveRequest $leave)
    {
        if ($leave->status !== LeaveStatus::PENDING) {
            return redirect()->back()->withErrors(['message' => 'ใบลานี้ไม่อยู่ในสถานะรออนุมัติ']);
        }

        // Authorization check
        $user = Auth::user();
        $employee = $user->employee;
        $role = $user->role->value;

        if (!$this->canApprove($role, $employee, $leave)) {
            return redirect()->back()->withErrors(['message' => 'คุณไม่มีสิทธิ์อนุมัติใบลานี้']);
        }

        $leave->update([
            'status'        => LeaveStatus::APPROVED->value,
            'approved_by'   => $employee?->id,
            'approved_at'   => Carbon::now(),
            'approval_note' => $request->input('note'),
        ]);

        return redirect()->back()->with('message', 'อนุมัติใบลาเรียบร้อยแล้ว');
    }

    /**
     * Reject a leave request.
     */
    public function reject(Request $request, LeaveRequest $leave)
    {
        if ($leave->status !== LeaveStatus::PENDING) {
            return redirect()->back()->withErrors(['message' => 'ใบลานี้ไม่อยู่ในสถานะรออนุมัติ']);
        }

        $request->validate([
            'note' => 'required|string|max:500',
        ], [
            'note.required' => 'กรุณาระบุเหตุผลที่ไม่อนุมัติ',
        ]);

        $user = Auth::user();
        $employee = $user->employee;
        $role = $user->role->value;

        if (!$this->canApprove($role, $employee, $leave)) {
            return redirect()->back()->withErrors(['message' => 'คุณไม่มีสิทธิ์ปฏิเสธใบลานี้']);
        }

        $leave->update([
            'status'        => LeaveStatus::REJECTED->value,
            'approved_by'   => $employee?->id,
            'approved_at'   => Carbon::now(),
            'approval_note' => $request->input('note'),
        ]);

        return redirect()->back()->with('message', 'ปฏิเสธใบลาเรียบร้อยแล้ว');
    }

    /**
     * Check if the current user can approve a given leave request.
     */
    private function canApprove(string $role, ?Employee $approver, LeaveRequest $leave): bool
    {
        // Admin/HR can approve anything
        if (in_array($role, ['admin', 'hr'])) {
            return true;
        }

        // Supervisor can approve direct subordinates
        if ($approver) {
            $leaveEmployee = $leave->employee;
            return $leaveEmployee && $leaveEmployee->supervisor_id === $approver->id;
        }

        return false;
    }
}
