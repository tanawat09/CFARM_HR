<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    protected $fillable = ['name', 'code', 'address', 'latitude', 'longitude', 'is_active'];
    protected function casts(): array { 
        return ['is_active' => 'boolean', 'latitude' => 'decimal:8', 'longitude' => 'decimal:8']; 
    }
    public function employees() { return $this->hasMany(Employee::class); }
    public function worksites() { return $this->hasMany(Worksite::class); }
}
