<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Shift;

class ShiftController extends Controller
{
    public function index(Request $request)
    {
        $shifts = Shift::paginate(15);
        return Inertia::render('Shift/Index', [
            'shifts' => $shifts
        ]);
    }

    public function create()
    {
        return Inertia::render('Shift/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:shifts,code',
            'start_time' => 'required',
            'end_time' => 'required',
            'late_after_minutes' => 'required|integer|min:0',
            'early_leave_before_minutes' => 'required|integer|min:0',
            'ot_after_minutes' => 'required|integer|min:0',
            'break_duration_minutes' => 'required|integer|min:0',
            'working_days' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->input('is_active', true);
        
        Shift::create($validated);

        return redirect()->route('shifts.index')->with('message', 'เพิ่มกะทำงานเรียบร้อยแล้ว');
    }

    public function edit(Shift $shift)
    {
        return Inertia::render('Shift/Edit', [
            'shift' => $shift
        ]);
    }

    public function update(Request $request, Shift $shift)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:shifts,code,' . $shift->id,
            'start_time' => 'required',
            'end_time' => 'required',
            'late_after_minutes' => 'required|integer|min:0',
            'early_leave_before_minutes' => 'required|integer|min:0',
            'ot_after_minutes' => 'required|integer|min:0',
            'break_duration_minutes' => 'required|integer|min:0',
            'working_days' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->input('is_active', true);
        
        $shift->update($validated);

        return redirect()->route('shifts.index')->with('message', 'แก้ไขกะทำงานเรียบร้อยแล้ว');
    }

    public function destroy(Shift $shift)
    {
        $shift->delete();
        return redirect()->route('shifts.index')->with('message', 'ลบกะทำงานเรียบร้อยแล้ว');
    }
}
