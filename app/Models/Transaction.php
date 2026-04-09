<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'category_id',
        'type', // 'income' ou 'expense'
        'amount',
        'description',
        'reference',
        'transaction_date',
        'payment_method',
        'status', // 'pending', 'completed', 'cancelled'
        'receipt_path',
        'metadata'
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'amount' => 'decimal:2',
        'metadata' => 'array'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
