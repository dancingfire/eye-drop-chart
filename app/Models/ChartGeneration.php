<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChartGeneration extends Model
{
    protected $fillable = [
        'user_id',
        'start_date',
        'surgery_date',
        'medication_count',
        'total_days',
        'format',
    ];

    protected $casts = [
        'start_date' => 'date',
        'surgery_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
