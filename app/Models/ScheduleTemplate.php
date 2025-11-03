<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleTemplate extends Model
{
    protected $fillable = ['name', 'description', 'template_data'];

    protected $casts = [
        'template_data' => 'array',
    ];
}
