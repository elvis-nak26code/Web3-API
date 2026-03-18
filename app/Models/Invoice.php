<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'company_id',
        'type', // 'incoming' (facture client) ou 'outgoing' (facture fournisseur)
        'invoice_number',
        'contact_name',
        'contact_email',
        'amount_ht',
        'amount_ttc',
        'vat_amount',
        'issue_date',
        'due_date',
        'status', // 'draft', 'sent', 'paid', 'overdue', 'cancelled'
        'file_path',
        'notes'
    ];

    protected $casts = [
        'issue_date' => 'datetime',
        'due_date' => 'datetime',
        'amount_ht' => 'decimal:2',
        'amount_ttc' => 'decimal:2',
        'vat_amount' => 'decimal:2'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}