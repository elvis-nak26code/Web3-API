<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    protected $fillable = [
        'company_id',
        'type', // 'budget_exceeded', 'low_cash', 'debt_reminder', 'invoice_overdue'
        'title',
        'message',
        'severity', // 'info', 'warning', 'danger'
        'is_read',
        'action_url',
        'metadata'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'metadata' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
