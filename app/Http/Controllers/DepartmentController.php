<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Department;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::withCount('employees')->orderBy('name')->get();
        return Inertia::render('Department/Index', ['departments' => $departments]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:departments,name',
            'code' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);
        $validated['code'] = $validated['code'] ?: null;
        Department::create($validated);
        return redirect()->back()->with('message', 'เพิ่มแผนกเรียบร้อย');
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:departments,name,' . $department->id,
            'code' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);
        $department->update($validated);
        return redirect()->back()->with('message', 'อัพเดทแผนกเรียบร้อย');
    }

    public function destroy(Department $department)
    {
        if ($department->employees()->count() > 0) {
            return redirect()->back()->withErrors(['delete' => 'ไม่สามารถลบได้ เนื่องจากมีพนักงานอยู่ในแผนกนี้']);
        }
        $department->delete();
        return redirect()->back()->with('message', 'ลบแผนกเรียบร้อย');
    }
}
