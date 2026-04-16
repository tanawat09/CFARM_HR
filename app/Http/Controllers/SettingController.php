<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\LeavePolicy;

class SettingController extends Controller
{
    public function index()
    {
        return Inertia::render('Settings/Index');
    }

    public function rolesSettings()
    {
        $roles = collect(\App\Enums\UserRole::cases())->map(fn($role) => [
            'value' => $role->value,
            'label' => $role->label(),
        ]);

        return Inertia::render('Settings/Roles', [
            'roles' => $roles,
        ]);
    }

    public function leaveSettings()
    {
        $policies = LeavePolicy::orderBy('sort_order')->get();

        return Inertia::render('Settings/Leave', [
            'policies' => $policies,
        ]);
    }

    public function storePolicy(Request $request)
    {
        $validated = $request->validate([
            'key'   => 'required|string|max:50|unique:leave_policies,key|regex:/^[a-z_]+$/',
            'name'  => 'required|string|max:100',
            'icon'  => 'required|string|max:10',
            'quota_days' => 'required|integer|min:0|max:365',
            'requires_reason' => 'boolean',
            'requires_attachment_after_days' => 'nullable|integer|min:1|max:30',
            'probation_allowed' => 'boolean',
            'is_tenure_based' => 'boolean',
            'tenure_threshold_years' => 'nullable|integer|min:1|max:50',
            'tenure_bonus_days' => 'nullable|integer|min:0|max:365',
            'is_active' => 'boolean',
        ], [
            'key.regex' => 'รหัสต้องเป็นภาษาอังกฤษตัวเล็กและขีดล่างเท่านั้น (เช่น sick_leave)',
            'key.unique' => 'รหัสนี้ถูกใช้งานแล้ว',
        ]);

        $maxSort = LeavePolicy::max('sort_order') ?? 0;
        $validated['sort_order'] = $maxSort + 1;

        LeavePolicy::create($validated);

        return redirect()->back()->with('message', 'เพิ่มประเภทการลาเรียบร้อยแล้ว');
    }

    public function updatePolicy(Request $request, LeavePolicy $policy)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:100',
            'icon'  => 'required|string|max:10',
            'quota_days' => 'required|integer|min:0|max:365',
            'requires_reason' => 'boolean',
            'requires_attachment_after_days' => 'nullable|integer|min:1|max:30',
            'probation_allowed' => 'boolean',
            'is_tenure_based' => 'boolean',
            'tenure_threshold_years' => 'nullable|integer|min:1|max:50',
            'tenure_bonus_days' => 'nullable|integer|min:0|max:365',
            'is_active' => 'boolean',
        ]);

        $policy->update($validated);

        return redirect()->back()->with('message', 'อัพเดทประเภทการลาเรียบร้อย');
    }

    public function destroyPolicy(LeavePolicy $policy)
    {
        // Prevent deleting built-in types that have existing leave_requests
        $usedCount = \App\Models\LeaveRequest::where('leave_type', $policy->key)->count();
        if ($usedCount > 0) {
            return redirect()->back()->withErrors(['delete' => "ไม่สามารถลบได้ เนื่องจากมีใบลาที่ใช้ประเภทนี้อยู่ {$usedCount} รายการ กรุณาปิดการใช้งานแทน"]);
        }

        $policy->delete();

        return redirect()->back()->with('message', 'ลบประเภทการลาเรียบร้อย');
    }

    public function lineSettings()
    {
        $channelSecret = \App\Models\Setting::where('key', 'LINE_CHANNEL_SECRET')->value('value') ?: config('services.line.channel_secret');
        $channelAccessToken = \App\Models\Setting::where('key', 'LINE_CHANNEL_ACCESS_TOKEN')->value('value') ?: config('services.line.channel_access_token');
        
        return Inertia::render('Settings/Line', [
            'channelSecret' => $channelSecret,
            'channelAccessToken' => $channelAccessToken,
            'webhookUrl' => route('line.webhook'),
        ]);
    }

    public function updateLineSettings(Request $request)
    {
        $validated = $request->validate([
            'channel_secret' => 'nullable|string|max:255',
            'channel_access_token' => 'nullable|string',
        ]);

        \App\Models\Setting::updateOrCreate(
            ['key' => 'LINE_CHANNEL_SECRET'],
            ['value' => $validated['channel_secret'], 'group' => 'line']
        );

        \App\Models\Setting::updateOrCreate(
            ['key' => 'LINE_CHANNEL_ACCESS_TOKEN'],
            ['value' => $validated['channel_access_token'], 'group' => 'line']
        );

    }

    public function holidaySettings()
    {
        $holidays = \App\Models\CompanyHoliday::orderBy('date', 'asc')->get();

        return Inertia::render('Settings/Holidays', [
            'holidays' => $holidays,
        ]);
    }

    public function storeHoliday(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date|unique:company_holidays,date',
            'name' => 'required|string|max:150',
        ], [
            'date.unique' => 'มีวันหยุดในวันนี้อยู่แล้ว',
        ]);

        \App\Models\CompanyHoliday::create($validated);

        return redirect()->back()->with('message', 'เพิ่มวันหยุดประจำปีเรียบร้อยแล้ว');
    }

    public function updateHoliday(Request $request, \App\Models\CompanyHoliday $holiday)
    {
        $validated = $request->validate([
            'date' => 'required|date|unique:company_holidays,date,' . $holiday->id,
            'name' => 'required|string|max:150',
        ], [
            'date.unique' => 'มีวันหยุดในวันนี้อยู่แล้ว',
        ]);

        $holiday->update($validated);

        return redirect()->back()->with('message', 'อัปเดตวันหยุดเรียบร้อยแล้ว');
    }

    public function destroyHoliday(\App\Models\CompanyHoliday $holiday)
    {
        $holiday->delete();

        return redirect()->back()->with('message', 'ลบวันหยุดเรียบร้อยแล้ว');
    }
}
