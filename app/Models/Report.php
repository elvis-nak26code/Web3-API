<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'title',
        'type',
        'data',
        'user_id',
        'generated_date',
        'format'
    ];

    protected $casts = [
        'data' => 'array',
        'generated_date' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}