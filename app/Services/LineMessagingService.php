<?php

namespace App\Services;

use App\Models\LeavePolicy;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class LineMessagingService
{
    private const PUSH_ENDPOINT = 'https://api.line.me/v2/bot/message/push';
    private const REPLY_ENDPOINT = 'https://api.line.me/v2/bot/message/reply';

    public function __construct(private readonly ?string $channelAccessToken = null)
    {
    }

    public function pushText(string $to, string $text): bool
    {
        $token = $this->channelAccessToken ?: config('services.line.channel_access_token');

        if (blank($token)) {
            Log::warning('LINE push skipped because LINE_CHANNEL_ACCESS_TOKEN is not configured.');

            return false;
        }

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->asJson()
                ->timeout(10)
                ->post(self::PUSH_ENDPOINT, $this->buildTextPayload($to, $text));

            if ($response->failed()) {
                Log::error('LINE push failed.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'to' => $to,
                ]);

                return false;
            }

            return true;
        } catch (Throwable $exception) {
            Log::error('LINE push threw an exception.', [
                'message' => $exception->getMessage(),
                'to' => $to,
            ]);

            return false;
        }
    }

    public function replyText(string $replyToken, string $text): bool
    {
        $token = $this->channelAccessToken ?: config('services.line.channel_access_token');

        if (blank($token)) {
            Log::warning('LINE reply skipped because LINE_CHANNEL_ACCESS_TOKEN is not configured.');

            return false;
        }

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->asJson()
                ->timeout(10)
                ->post(self::REPLY_ENDPOINT, $this->buildReplyPayload($replyToken, $text));

            if ($response->failed()) {
                Log::error('LINE reply failed.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            return true;
        } catch (Throwable $exception) {
            Log::error('LINE reply threw an exception.', [
                'message' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    public function buildTextPayload(string $to, string $text): array
    {
        return [
            'to' => $to,
            'messages' => [
                [
                    'type' => 'text',
                    'text' => $text,
                ],
            ],
        ];
    }

    public function buildReplyPayload(string $replyToken, string $text): array
    {
        return [
            'replyToken' => $replyToken,
            'messages' => [
                [
                    'type' => 'text',
                    'text' => $text,
                ],
            ],
        ];
    }

    public function formatLeaveRequestMessage(LeaveRequest $leave): string
    {
        $employee = $leave->employee;
        $leaveType = (string) $leave->getRawOriginal('leave_type');
        $leaveTypeName = LeavePolicy::where('key', $leaveType)->value('name') ?: $leaveType;
        $department = $employee?->department?->name ?: '-';
        $dateRange = $this->formatLeaveDateRange($leave);
        $totalDays = rtrim(rtrim(number_format((float) $leave->total_days, 3, '.', ''), '0'), '.');
        $reason = Str::limit((string) ($leave->reason ?: '-'), 300);

        return implode("\n", [
            'มีคำขอลาใหม่รออนุมัติ',
            'พนักงาน: ' . trim(($employee?->first_name ?: '') . ' ' . ($employee?->last_name ?: '')) . ' (' . ($employee?->employee_code ?: '-') . ')',
            'แผนก: ' . $department,
            'ประเภทลา: ' . $leaveTypeName,
            'ช่วงลา: ' . $dateRange,
            'จำนวน: ' . ($totalDays ?: '0') . ' วัน',
            'เหตุผล: ' . $reason,
            'อนุมัติได้ที่: ' . route('leave.approvals'),
        ]);
    }

    private function formatLeaveDateRange(LeaveRequest $leave): string
    {
        $startDate = $leave->start_date?->format('Y-m-d') ?: '-';

        if ($leave->leave_format === 'hourly') {
            return sprintf(
                '%s %s-%s',
                $startDate,
                substr((string) $leave->start_time, 0, 5),
                substr((string) $leave->end_time, 0, 5),
            );
        }

        $endDate = $leave->end_date?->format('Y-m-d') ?: '-';

        return $startDate === $endDate ? $startDate : $startDate . ' ถึง ' . $endDate;
    }
}
