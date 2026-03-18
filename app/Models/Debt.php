<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Debt extends Model
{
    protected $fillable = [
        'company_id',
        'type', // 'receivable' (à recevoir) ou 'payable' (à payer)
        'contact_name',
        'contact_phone',
        'contact_email',
        'amount',
        'remaining_amount',
        'description',
        'due_date',
        'status', // 'pending', 'partial', 'paid', 'overdue'
        'reminder_sent_at'
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'reminder_sent_at' => 'datetime'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function payments()
    {
        return $this->hasMany(DebtPayment::class);
    }
}