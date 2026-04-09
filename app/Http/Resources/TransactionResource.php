<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'amount' => $this->amount,
            'formatted_amount' => number_format($this->amount, 2) . ' €',
            'description' => $this->description,
            'date' => $this->transaction_date->format('Y-m-d'),
            'formatted_date' => $this->transaction_date->format('d/m/Y'),
            'payment_method' => $this->payment_method,
            'status' => $this->status,
            'receipt_url' => $this->receipt_path ? asset('storage/' . $this->receipt_path) : null,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name
            ],
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
