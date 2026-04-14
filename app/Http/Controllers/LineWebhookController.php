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
            Log::warning('Rejected LINE webhook because the signature is invalid.');

            return response('Invalid signature', 403);
        }

        foreach ((array) $request->input('events', []) as $event) {
            $userId = data_get($event, 'source.userId');
            $replyToken = data_get($event, 'replyToken');

            if (!$userId) {
                continue;
            }

            Log::info('Received LINE webhook userId.', [
                'line_user_id' => $userId,
                'event_type' => data_get($event, 'type'),
            ]);

            if ($replyToken) {
                $line->replyText($replyToken, implode("\n", [
                    'LINE userId ของคุณคือ',
                    $userId,
                    '',
                    'นำค่านี้ไปกรอกในช่อง LINE userId ของข้อมูลพนักงานหัวหน้างาน',
                ]));
            }
        }

        return response('', 200);
    }

    private function isValidSignature(string $body, string $signature): bool
    {
        $secret = (string) config('services.line.channel_secret');

        if (blank($secret) || blank($signature)) {
            return false;
        }

        $expected = base64_encode(hash_hmac('sha256', $body, $secret, true));

        return hash_equals($expected, $signature);
    }
}
