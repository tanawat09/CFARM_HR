<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Worksite extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    use \Illuminate\Database\Eloquent\SoftDeletes;
    protected $fillable = ['name', 'code', 'address', 'latitude', 'longitude', 'radius_meters', 'geofence_type', 'polygon_coordinates', 'is_active', 'branch_id'];
    protected function casts(): array { 
        return ['is_active' => 'boolean', 'latitude' => 'decimal:8', 'longitude' => 'decimal:8', 'polygon_coordinates' => 'array']; 
    }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function employees() { return $this->belongsToMany(Employee::class, 'worksite_employee')->withPivot('assigned_from', 'assigned_to')->withTimestamps(); }
}
