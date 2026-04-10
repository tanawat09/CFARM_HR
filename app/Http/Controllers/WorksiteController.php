<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Worksite;
use App\Models\Branch;

class WorksiteController extends Controller
{
    public function index(Request $request)
    {
        $query = Worksite::with(['branch'])->withCount('employees');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $worksites = $query->paginate(12)->withQueryString();

        return Inertia::render('Worksite/Index', [
            'worksites' => $worksites,
            'branches' => Branch::all(),
            'filters' => $request->only(['search'])
        ]);
    }
    public function create()
    {
        return Inertia::render('Worksite/Create', [
            'branches' => Branch::all()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:worksites,code',
            'address' => 'nullable|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius_meters' => 'required|integer|min:10',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $validated['geofence_type'] = 'circle';
        $validated['is_active'] = true;
        
        Worksite::create($validated);

        // Flash message handling usually works out of box with Inertia shared props
        return redirect()->route('worksites.index');
    }

    public function edit(Worksite $worksite)
    {
        return Inertia::render('Worksite/Edit', [
            'worksite' => $worksite,
            'branches' => Branch::all()
        ]);
    }

    public function update(Request $request, Worksite $worksite)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:worksites,code,' . $worksite->id,
            'address' => 'nullable|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius_meters' => 'required|integer|min:10',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $worksite->update($validated);

        return redirect()->route('worksites.index')->with('message', 'อัพเดทไซต์งานเรียบร้อย');
    }
}
