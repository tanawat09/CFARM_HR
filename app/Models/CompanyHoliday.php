<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyHoliday extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = ['date', 'name'];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }
}
