<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name',
        'siret',
        'address',
        'phone',
        'email',
        'subscription_type',
        'subscription_end_date',
        'settings'
    ];

    protected $casts = [
        'subscription_end_date' => 'datetime',
        'settings' => 'array'
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function debts()
    {
        return $this->hasMany(Debt::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function alerts()
    {
        return $this->hasMany(Alert::class);
    }
}