<?php

namespace App\Jobs;

use App\Models\LeaveRequest;
use App\Services\LineMessagingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendLeaveRequestLineNotification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(public readonly int $leaveRequestId)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(LineMessagingService $line): void
    {
        $leave = LeaveRequest::with(['employee.department', 'employee.supervisor'])->find($this->leaveRequestId);

        if (!$leave) {
            Log::warning('LINE leave notification skipped because the leave request was not found.', [
                'leave_request_id' => $this->leaveRequestId,
            ]);

            return;
        }

        $employee = $leave->employee;
        $supervisor = $employee?->supervisor;

        if (!$supervisor) {
            Log::warning('LINE leave notification skipped because the employee has no supervisor.', [
                'leave_request_id' => $leave->id,
                'employee_id' => $employee?->id,
            ]);

            return;
        }

        $lineUserId = trim((string) $supervisor->line_user_id);

        if ($lineUserId === '') {
            Log::warning('LINE leave notification skipped because the supervisor has no line_user_id.', [
                'leave_request_id' => $leave->id,
                'employee_id' => $employee?->id,
                'supervisor_id' => $supervisor->id,
            ]);

            return;
        }

        $line->pushText($lineUserId, $line->formatLeaveRequestMessage($leave));
    }
}
