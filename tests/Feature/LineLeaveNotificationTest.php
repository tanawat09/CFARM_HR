<?php

namespace Tests\Feature;

use App\Enums\LeaveStatus;
use App\Jobs\SendLeaveRequestLineNotification;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeavePolicy;
use App\Models\LeaveRequest;
use App\Models\Position;
use App\Models\Shift;
use App\Models\User;
use App\Services\LineMessagingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class LineLeaveNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_leave_request_dispatches_line_notification_job_for_supervisor(): void
    {
        Queue::fake();

        $supervisor = $this->createEmployee(lineUserId: 'U1234567890');
        $employee = $this->createEmployee(supervisor: $supervisor);
        $policy = $this->createLeavePolicy();

        $response = $this->actingAs($employee->user)->post(route('leave.store'), [
            'leave_type' => $policy->key,
            'leave_format' => 'daily',
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
            'reason' => 'Family errand',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('leave.index', absolute: false));

        $leave = LeaveRequest::firstOrFail();

        Queue::assertPushed(SendLeaveRequestLineNotification::class, function (SendLeaveRequestLineNotification $job) use ($leave) {
            return $job->leaveRequestId === $leave->id;
        });
    }

    public function test_leave_request_still_succeeds_without_supervisor_line_user_id(): void
    {
        Queue::fake();

        $supervisor = $this->createEmployee();
        $employee = $this->createEmployee(supervisor: $supervisor);
        $policy = $this->createLeavePolicy();

        $response = $this->actingAs($employee->user)->post(route('leave.store'), [
            'leave_type' => $policy->key,
            'leave_format' => 'daily',
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'reason' => 'Personal business',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('leave.index', absolute: false));

        $this->assertDatabaseHas('leave_requests', [
            'employee_id' => $employee->id,
            'status' => LeaveStatus::PENDING->value,
        ]);
    }

    public function test_line_payload_and_hourly_leave_message_are_formatted(): void
    {
        $employee = $this->createEmployee();
        $policy = $this->createLeavePolicy(name: 'ลากิจ');
        $leave = LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type' => $policy->key,
            'leave_format' => 'hourly',
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '11:00',
            'total_days' => 0.25,
            'reason' => 'Go to the bank',
            'status' => LeaveStatus::PENDING->value,
        ]);

        $service = new LineMessagingService('token');
        $message = $service->formatLeaveRequestMessage($leave->load('employee.department'));
        $payload = $service->buildTextPayload('U1234567890', $message);

        $this->assertSame('U1234567890', $payload['to']);
        $this->assertSame('text', $payload['messages'][0]['type']);
        $this->assertStringContainsString('มีคำขอลาใหม่รออนุมัติ', $payload['messages'][0]['text']);
        $this->assertStringContainsString('ลากิจ', $payload['messages'][0]['text']);
        $this->assertStringContainsString('09:00-11:00', $payload['messages'][0]['text']);
        $this->assertStringContainsString('0.25 วัน', $payload['messages'][0]['text']);
    }

    public function test_line_push_success_and_error_do_not_throw(): void
    {
        config(['services.line.channel_access_token' => 'test-token']);

        Http::fake([
            'https://api.line.me/*' => Http::sequence()
                ->push([], 200)
                ->push(['message' => 'failed'], 500),
        ]);

        $service = new LineMessagingService();

        $this->assertTrue($service->pushText('U1234567890', 'hello'));

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.line.me/v2/bot/message/push'
                && $request['to'] === 'U1234567890'
                && $request['messages'][0]['text'] === 'hello'
                && $request->hasHeader('Authorization', 'Bearer test-token');
        });

        Log::spy();

        $this->assertFalse($service->pushText('U1234567890', 'hello'));

        Log::shouldHaveReceived('error')->once();
    }

    public function test_line_webhook_replies_with_user_id_when_signature_is_valid(): void
    {
        config([
            'services.line.channel_access_token' => 'test-token',
            'services.line.channel_secret' => 'test-secret',
        ]);

        Http::fake([
            'https://api.line.me/*' => Http::response([], 200),
        ]);

        $payload = [
            'events' => [
                [
                    'type' => 'message',
                    'replyToken' => 'reply-token',
                    'source' => [
                        'type' => 'user',
                        'userId' => 'U1234567890',
                    ],
                ],
            ],
        ];
        $body = json_encode($payload, JSON_UNESCAPED_UNICODE);

        $response = $this->postJson(route('line.webhook'), $payload, [
            'X-Line-Signature' => $this->lineSignature($body, 'test-secret'),
        ]);

        $response->assertOk();

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.line.me/v2/bot/message/reply'
                && $request['replyToken'] === 'reply-token'
                && str_contains($request['messages'][0]['text'], 'U1234567890')
                && $request->hasHeader('Authorization', 'Bearer test-token');
        });
    }

    public function test_line_webhook_rejects_invalid_signature(): void
    {
        config(['services.line.channel_secret' => 'test-secret']);
        Http::fake();

        $response = $this
            ->withHeaders(['X-Line-Signature' => 'invalid'])
            ->postJson(route('line.webhook'), ['events' => []]);

        $response->assertForbidden();

        Http::assertNothingSent();
    }

    private function createEmployee(?Employee $supervisor = null, ?string $lineUserId = null): Employee
    {
        $department = Department::firstOrCreate(
            ['code' => 'OPS'],
            ['name' => 'Operations', 'is_active' => true],
        );

        $position = Position::firstOrCreate(
            ['name' => 'Staff'],
            ['is_active' => true],
        );

        $shift = Shift::firstOrCreate(
            ['code' => 'DAY'],
            [
                'name' => 'Day shift',
                'start_time' => '08:00',
                'end_time' => '17:00',
                'working_days' => [1, 2, 3, 4, 5],
                'is_active' => true,
            ],
        );

        return Employee::create([
            'user_id' => User::factory()->create()->id,
            'employee_code' => fake()->unique()->bothify('EMP###'),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'department_id' => $department->id,
            'position_id' => $position->id,
            'branch_id' => null,
            'supervisor_id' => $supervisor?->id,
            'line_user_id' => $lineUserId,
            'shift_id' => $shift->id,
            'employment_status' => 'active',
            'check_in_mode' => 'free',
            'hire_date' => now()->subYear()->toDateString(),
        ]);
    }

    private function lineSignature(string $body, string $secret): string
    {
        return base64_encode(hash_hmac('sha256', $body, $secret, true));
    }

    private function createLeavePolicy(string $name = 'Personal leave'): LeavePolicy
    {
        return LeavePolicy::firstOrCreate(
            ['key' => 'personal'],
            [
                'name' => $name,
                'icon' => 'P',
                'quota_days' => 10,
                'requires_reason' => true,
                'probation_allowed' => true,
                'is_active' => true,
                'sort_order' => 1,
            ],
        );
    }
}
