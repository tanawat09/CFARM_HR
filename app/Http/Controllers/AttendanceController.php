<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AttendanceService;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Show the Check-in / Check-out interface
     */
    public function index()
    {
        $employee = Auth::user()->employee;
        if (!$employee) abort(403, 'User is not an employee');

        $todayAttendance = \App\Models\Attendance::where('employee_id', $employee->id)
            ->where('date', Carbon::today()->format('Y-m-d'))
            ->first();

        // Needs Check-In or Check-Out
        $mode = $todayAttendance ? 'checkout' : 'checkin';
        if ($todayAttendance && $todayAttendance->status->value === 'checked_out') {
            $mode = 'completed';
        }

        return Inertia::render('Attendance/CheckInOut', [
            'mode' => $mode,
            'employee' => $employee->load('worksites', 'shift'),
            'today_attendance' => $todayAttendance
        ]);
    }

    /**
     * Process Check-in
     */
    public function checkIn(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'accuracy' => 'nullable|numeric',
            'photo' => 'required|image|max:5120', // Max 5MB
            'note' => 'nullable|string|max:500',
            'outside_reason' => 'nullable|string|max:500',
        ]);

        $employee = Auth::user()->employee;
        if (!$employee) return back()->withErrors(['message' => 'คุณยังไม่ได้เชื่อมโยงข้อมูลพนักงาน']);

        try {
            // Upload photo
            $photoPath = $request->file('photo')->store('attendances/check_in', 'public');
            
            $data = $request->only(['latitude', 'longitude', 'accuracy', 'note', 'outside_reason']);
            $data['photo_path'] = $photoPath;

            $this->attendanceService->checkIn($employee, $data);

            return back()->with('success', 'ลงเวลาเข้างานสำเร็จ');

        } catch (\Exception $e) {
            // Rollback uploaded file if needed
            if (isset($photoPath)) Storage::disk('public')->delete($photoPath);
            return back()->withErrors(['message' => $e->getMessage()]);
        }
    }

    /**
     * Process Check-out
     */
    public function checkOut(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'accuracy' => 'nullable|numeric',
            'photo' => 'required|image|max:5120',
            'note' => 'nullable|string|max:500',
            'outside_reason' => 'nullable|string|max:500',
        ]);

        $employee = Auth::user()->employee;
        
        try {
            $photoPath = $request->file('photo')->store('attendances/check_out', 'public');
            
            $data = $request->only(['latitude', 'longitude', 'accuracy', 'note', 'outside_reason']);
            $data['photo_path'] = $photoPath;

            $this->attendanceService->checkOut($employee, $data);

            return back()->with('success', 'ลงเวลาออกงานสำเร็จ');

        } catch (\Exception $e) {
            if (isset($photoPath)) Storage::disk('public')->delete($photoPath);
            return back()->withErrors(['message' => $e->getMessage()]);
        }
    }

    /**
     * Show Attendance History
     */
    public function history(Request $request)
    {
        $employee = Auth::user()->employee;
        
        $query = \App\Models\Attendance::where('employee_id', $employee->id)
            ->with(['shift', 'checkInWorksite'])
            ->orderBy('date', 'desc');
            
        // Filter by month/year if provided
        if ($request->has('month')) {
            $query->whereMonth('date', $request->month);
            $query->whereYear('date', $request->year ?? Carbon::now()->year);
        }

        $attendances = $query->paginate(20);

        return Inertia::render('Attendance/History', [
            'attendances' => $attendances
        ]);
    }
}
