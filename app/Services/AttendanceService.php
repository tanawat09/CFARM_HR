<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Shift;
use Carbon\Carbon;
use Exception;
use App\Enums\AttendanceStatus;
use App\Enums\CheckInMode;

class AttendanceService
{
    protected $geofenceService;

    public function __construct(GeofenceService $geofenceService)
    {
        $this->geofenceService = $geofenceService;
    }

    /**
     * Handle Employee Check In
     */
    public function checkIn(Employee $employee, array $data)
    {
        $date = Carbon::today();
        
        // 1. Check if already checked in today
        $existing = Attendance::where('employee_id', $employee->id)
            ->where('date', $date->format('Y-m-d'))
            ->first();
            
        if ($existing) {
            throw new Exception("คุณได้ลงเวลาเข้างานของวันนี้ไปแล้ว");
        }

        // 2. Validate Geofence if required
        $isWithinGeofence = null;
        if ($employee->check_in_mode === CheckInMode::GEOFENCE) {
            // Find assigned worksite
            $worksite = $employee->worksites()->first();
            if (!$worksite) {
                throw new Exception("ไม่พบข้อมูลสถานที่ปฏิบัติงานที่ได้รับมอบหมาย");
            }
            
            $data['check_in_worksite_id'] = $worksite->id;
            
            $isWithinGeofence = $this->geofenceService->isWithinRadius(
                $data['latitude'],
                $data['longitude'],
                $worksite->latitude,
                $worksite->longitude,
                $worksite->radius_meters
            );
            
            if (!$isWithinGeofence && empty($data['outside_reason'])) {
                throw new Exception("คุณอยู่นอกพื้นที่ที่กำหนด โปรดระบุเหตุผล");
            }
        }

        // 3. Calculate Lateness
        $shift = $employee->shift;
        if (!$shift) {
            throw new Exception("ไม่พบข้อมูลกะการทำงาน");
        }

        $now = Carbon::now();
        $shiftStart = Carbon::parse($shift->start_time);
        
        $isLate = false;
        $lateMinutes = 0;
        
        if ($now->greaterThan($shiftStart->copy()->addMinutes($shift->late_after_minutes))) {
            $isLate = true;
            $lateMinutes = $now->diffInMinutes($shiftStart);
        }

        // 4. Save Attendance
        return Attendance::create([
            'employee_id' => $employee->id,
            'date' => $date->format('Y-m-d'),
            'shift_id' => $shift->id,
            'check_in_at' => $now,
            'check_in_latitude' => $data['latitude'],
            'check_in_longitude' => $data['longitude'],
            'check_in_accuracy' => $data['accuracy'] ?? null,
            'check_in_photo' => $data['photo_path'] ?? null,
            'check_in_note' => $data['note'] ?? null,
            'check_in_worksite_id' => $data['check_in_worksite_id'] ?? null,
            'check_in_within_geofence' => $isWithinGeofence,
            'check_in_outside_reason' => $data['outside_reason'] ?? null,
            'status' => AttendanceStatus::CHECKED_IN,
            'is_late' => $isLate,
            'late_minutes' => $lateMinutes,
            'ip_address' => request()->ip(),
            'device_info' => request()->userAgent(),
        ]);
    }

    /**
     * Handle Employee Check Out
     */
    public function checkOut(Employee $employee, array $data)
    {
        $date = Carbon::today();
        
        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $date->format('Y-m-d'))
            ->first();
            
        if (!$attendance) {
            throw new Exception("ไม่พบข้อมูลลงเวลาเข้างานของวันนี้");
        }
        
        if ($attendance->status === AttendanceStatus::CHECKED_OUT) {
            throw new Exception("คุณได้ลงเวลาออกงานของวันนี้ไปแล้ว");
        }

        // 1. Validate Geofence if required
        $isWithinGeofence = null;
        if ($employee->check_in_mode === CheckInMode::GEOFENCE) {
            $worksite = $attendance->checkInWorksite ?? $employee->worksites()->first();
            
            if ($worksite) {
                $data['check_out_worksite_id'] = $worksite->id;
                $isWithinGeofence = $this->geofenceService->isWithinRadius(
                    $data['latitude'],
                    $data['longitude'],
                    $worksite->latitude,
                    $worksite->longitude,
                    $worksite->radius_meters
                );
                
                if (!$isWithinGeofence && empty($data['outside_reason'])) {
                    throw new Exception("คุณอยู่นอกพื้นที่ที่กำหนด โปรดระบุเหตุผล");
                }
            }
        }

        // 2. Calculate Working Time & OT
        $shift = $attendance->shift;
        $now = Carbon::now();
        $shiftEnd = Carbon::parse($shift->end_time);
        
        $isEarlyLeave = false;
        $earlyLeaveMinutes = 0;
        
        // Check Early Leave
        if ($now->lessThan($shiftEnd->copy()->subMinutes($shift->early_leave_before_minutes))) {
            $isEarlyLeave = true;
            $earlyLeaveMinutes = $shiftEnd->diffInMinutes($now);
        }

        // Check Working Minutes
        $workingMinutes = $now->diffInMinutes($attendance->check_in_at) - $shift->break_duration_minutes;
        if ($workingMinutes < 0) $workingMinutes = 0;

        // Check OT
        $otMinutes = 0;
        if ($now->greaterThan($shiftEnd->copy()->addMinutes($shift->ot_after_minutes))) {
            $otMinutes = $now->diffInMinutes($shiftEnd);
        }

        // 3. Update Attendance
        $attendance->update([
            'check_out_at' => $now,
            'check_out_latitude' => $data['latitude'],
            'check_out_longitude' => $data['longitude'],
            'check_out_accuracy' => $data['accuracy'] ?? null,
            'check_out_photo' => $data['photo_path'] ?? null,
            'check_out_note' => $data['note'] ?? null,
            'check_out_worksite_id' => $data['check_out_worksite_id'] ?? null,
            'check_out_within_geofence' => $isWithinGeofence,
            'check_out_outside_reason' => $data['outside_reason'] ?? null,
            'status' => AttendanceStatus::CHECKED_OUT,
            'is_early_leave' => $isEarlyLeave,
            'early_leave_minutes' => $earlyLeaveMinutes,
            'working_minutes' => $workingMinutes,
            'ot_minutes' => $otMinutes,
        ]);

        return $attendance;
    }
}
