<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Position;

class PositionController extends Controller
{
    public function index()
    {
        $positions = Position::withCount('employees')->orderBy('name')->get();
        return Inertia::render('Position/Index', ['positions' => $positions]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:positions,name',
            'is_active' => 'boolean',
        ]);
        Position::create($validated);
        return redirect()->back()->with('message', 'เพิ่มตำแหน่งเรียบร้อย');
    }

    public function update(Request $request, Position $position)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:positions,name,' . $position->id,
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
