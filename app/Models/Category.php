<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'type', // 'income' ou 'expense'
        'color',
        'icon',
        'budget_limit',
        'is_active'
    ];

    protected $casts = [
        'budget_limit' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}