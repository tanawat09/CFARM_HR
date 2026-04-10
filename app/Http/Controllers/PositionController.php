<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Position;
use App\Models\Department;

class PositionController extends Controller
{
    public function index()
    {
        $positions = Position::with('department')->withCount('employees')->orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        return Inertia::render('Position/Index', ['positions' => $positions, 'departments' => $departments]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'department_id' => 'nullable|exists:departments,id',
            'is_active' => 'boolean',
        ]);
        Position::create($validated);
        return redirect()->back()->with('message', 'เพิ่มตำแหน่งเรียบร้อย');
    }

    public function update(Request $request, Position $position)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'department_id' => 'nullable|exists:departments,id',
            'is_active' => 'boolean',
        ]);
        $position->update($validated);
        return redirect()->back()->with('message', 'อัพเดทตำแหน่งเรียบร้อย');
    }

    public function destroy(Position $position)
    {
        if ($position->employees()->count() > 0) {
            return redirect()->back()->withErrors(['delete' => 'ไม่สามารถลบได้ เนื่องจากมีพนักงานอยู่ในตำแหน่งนี้']);
        }
        $position->delete();
        return redirect()->back()->with('message', 'ลบตำแหน่งเรียบร้อย');
    }
}
