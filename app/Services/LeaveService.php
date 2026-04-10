<?php

namespace App\Services;

use App\Models\LeaveRequest;
use App\Models\LeaveBalance;
use App\Models\Employee;
use Exception;
use App\Enums\LeaveStatus;
use Carbon\Carbon;

class LeaveService
{
    public function requestLeave(Employee $employee, array $data)
    {
        $year = Carbon::parse($data['start_date'])->year;
        
        $balance = LeaveBalance::where('employee_id', $employee->id)
            ->where('leave_type_id', $data['leave_type_id'])
            ->where('year', $year)
            ->first();
            
        if (!$balance) {
            throw new Exception("ไม่พบโควตาวันลาประเภทที่เลือกในปีนี้");
        }
        
        if ($balance->remaining_days < $data['total_days']) {
            throw new Exception("คุณมีโควตาวันลาเหลือไม่เพียงพอ (เหลือ {$balance->remaining_days} วัน)");
        }

        return LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $data['leave_type_id'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'total_days' => $data['total_days'],
            'reason' => $data['reason'],
            'status' => LeaveStatus::PENDING,
        ]);
    }

    public function approveLeave(LeaveRequest $request, Employee $approver, string $note = null)
    {
        if ($request->status !== LeaveStatus::PENDING) {
            throw new Exception("คำขอนี้ไม่ได้อยู่ในสถานะรออนุมัติ");
        }

        $year = Carbon::parse($request->start_date)->year;
        
        $balance = LeaveBalance::where('employee_id', $request->employee_id)
            ->where('leave_type_id', $request->leave_type_id)
            ->where('year', $year)
            ->first();

        // Update balance
        $balance->used_days += $request->total_days;
        $balance->remaining_days -= $request->total_days;
        $balance->save();

        // Update request
        $request->update([
            'status' => LeaveStatus::APPROVED,
            'approved_by' => $approver->id,
            'approved_at' => Carbon::now(),
            'approval_note' => $note,
        ]);

        return $request;
    }
}
