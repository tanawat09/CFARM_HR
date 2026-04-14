<?php

namespace App\Http\Controllers;

use App\Services\LineMessagingService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class LineWebhookController extends Controller
{
    public function __invoke(Request $request, LineMessagingService $line): Response
    {
        $body = $request->getContent();

        if (!$this->isValidSignature($body, (string) $request->header('X-Line-Signature'))) {
            Log::warning('Rejected LINE webhook because the signature is invalid.', [
                'line_secret_fingerprint' => $this->lineSecretFingerprint(),
                'body_sha256' => hash('sha256', $body),
                'signature_length' => strlen((string) $request->header('X-Line-Signature')),
            ]);

            return response('Invalid signature', 403);
        }

        foreach ((array) $request->input('events', []) as $event) {
            $eventType = data_get($event, 'type');

            if ($eventType === 'postback') {
                $this->handlePostback($event, $line);
            } elseif ($eventType === 'message') {
                $this->handleMessage($event, $line);
            }
        }

        return response('', 200);
    }

    private function handleMessage(array $event, LineMessagingService $line): void
    {
        $userId = data_get($event, 'source.userId');
        $replyToken = data_get($event, 'replyToken');

        if ($userId && $replyToken) {
            $line->replyText($replyToken, implode("\n", [
                'LINE userId ของคุณคือ',
                $userId,
                '',
                'นำค่านี้ไปกรอกในช่อง LINE userId ของข้อมูลพนักงานหัวหน้างาน',
            ]));
        }
    }

    private function handlePostback(array $event, LineMessagingService $line): void
    {
        $userId = data_get($event, 'source.userId');
        $replyToken = data_get($event, 'replyToken');
        $data = data_get($event, 'postback.data', '');

        parse_str($data, $parsedData);
        $action = $parsedData['action'] ?? null;
        $leaveId = $parsedData['id'] ?? null;

        if (!$userId || !$replyToken || !$action || !$leaveId) {
            return;
        }

        $supervisor = \App\Models\Employee::where('line_user_id', $userId)->first();
        if (!$supervisor) {
            $line->replyText($replyToken, '❌ ไม่พบข้อมูลพนักงานของคุณในระบบ กรุณาตรวจสอบ LINE userId ในระบบ HR');
            return;
        }

        $leave = \App\Models\LeaveRequest::find($leaveId);
        if (!$leave) {
            $line->replyText($replyToken, '❌ ไม่พบข้อมูลคำขอลานี้ หรือคำขอลาถูกลบไปแล้ว');
            return;
        }

        if ($leave->status !== \App\Enums\LeaveStatus::PENDING->value) {
            $line->replyText($replyToken, '⚠️ คำขอลานี้ได้ถูกดำเนินการอนุมัติ/ไม่อนุมัติ ไปเรียบร้อยแล้ว');
            return;
        }

        // Verify supervisor
        if ($leave->employee->supervisor_id !== $supervisor->id) {
            $line->replyText($replyToken, '❌ คุณไม่มีสิทธิ์อนุมัติคำขอลานี้ เนื่องจากไม่ได้เป็นหัวหน้างานโดยตรง');
            return;
        }

        // Process Action
        if ($action === 'approve') {
            $leave->update([
                'status' => \App\Enums\LeaveStatus::APPROVED->value,
                'approved_by' => $supervisor->id,
                'approved_at' => now(),
            ]);
            $line->replyText($replyToken, '✅ ทำรายการ "อนุมัติ" คำขอลารายการที่ ' . $leaveId . ' สำเร็จแล้ว!');
        } elseif ($action === 'reject') {
            $leave->update([
                'status' => \App\Enums\LeaveStatus::REJECTED->value,
                'approved_by' => $supervisor->id,
                'approved_at' => now(),
                'approval_note' => 'ทำรายการผ่าน LINE',
            ]);
            $line->replyText($replyToken, '❌ ทำรายการ "ไม่อนุมัติ" คำขอลารายการที่ ' . $leaveId . ' สำเร็จแล้ว');
        }
    }

    private function isValidSignature(string $body, string $signature): bool
    {
        $secret = (string) (\App\Models\Setting::where('key', 'LINE_CHANNEL_SECRET')->value('value') ?: config('services.line.channel_secret'));

        if (blank($secret) || blank($signature)) {
            return false;
        }

        $expected = base64_encode(hash_hmac('sha256', $body, $secret, true));

        return hash_equals($expected, $signature);
    }

    private function lineSecretFingerprint(): ?string
    {
        $secret = (string) (\App\Models\Setting::where('key', 'LINE_CHANNEL_SECRET')->value('value') ?: config('services.line.channel_secret'));

        return blank($secret) ? null : substr(hash('sha256', $secret), 0, 12);
    }
}
