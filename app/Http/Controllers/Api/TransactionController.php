<?php

namespace App\Http\Controllers\Api;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use Illuminate\Support\Facades\Storage;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $transactions = Transaction::where('company_id', $request->user()->company_id)
            ->with(['category', 'user'])
            ->when($request->type, function ($query, $type) {
                return $query->where('type', $type);
            })
            ->when($request->category_id, function ($query, $categoryId) {
                return $query->where('category_id', $categoryId);
            })
            ->when($request->start_date, function ($query, $date) {
                return $query->whereDate('transaction_date', '>=', $date);
            })
            ->when($request->end_date, function ($query, $date) {
                return $query->whereDate('transaction_date', '<=', $date);
            })
            ->latest('transaction_date')
            ->paginate($request->per_page ?? 15);

        return TransactionResource::collection($transactions);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'date' => 'required|date',
            'payment_method' => 'required|string|max:50',
        ]);

        $validated['company_id'] = $request->user()->company_id;
        $validated['user_id'] = $request->user()->id;
        $validated['transaction_date'] = $validated['date'];
        unset($validated['date']);
        $validated['status'] = 'completed';

        // Générer une référence unique
        do {
            $validated['reference'] = 'TXN-' . date('Y') . '-' . strtoupper(substr(md5(uniqid()), 0, 8));
        } while (Transaction::where('reference', $validated['reference'])->exists());

        \Log::info('Validated data:', $validated);

        $transaction = Transaction::create($validated);

        // Déclencher la vérification des alertes
        // $this->checkForAlerts($transaction);

        return new TransactionResource($transaction);
    }

    public function show(Transaction $transaction)
    {
        $this->authorize('update', $transaction);

        $validated = $request->validate([
            'type' => 'sometimes|in:income,expense',
            'amount' => 'sometimes|numeric|min:0',
            'description' => 'sometimes|string|max:255',
            'category_id' => 'sometimes|exists:categories,id',
            'date' => 'sometimes|date',
            'payment_method' => 'sometimes|string|max:50',
        ]);

        $transaction->update($validated);

        return new TransactionResource($transaction);
    }

    public function destroy(Transaction $transaction)
    {
        $this->authorize('delete', $transaction);
        $transaction->delete();

        return response()->json([
            'success' => true,
            'message' => 'Transaction supprimée avec succès'
        ]);
    }

    public function uploadReceipt(Request $request, Transaction $transaction)
    {
        $this->authorize('update', $transaction);

        $request->validate([
            'receipt' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120'
        ]);

        $path = $request->file('receipt')->store('receipts', 'public');

        $transaction->update([
            'receipt_path' => $path
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Reçu téléchargé avec succès',
            'receipt_url' => Storage::url($path)
        ]);
    }

    public function summary(Request $request)
    {
        $companyId = $request->user()->company_id;

        $income = Transaction::where('company_id', $companyId)
            ->where('type', 'income')
            ->whereMonth('date', now()->month)
            ->sum('amount');

        $expenses = Transaction::where('company_id', $companyId)
            ->where('type', 'expense')
            ->whereMonth('date', now()->month)
            ->sum('amount');

        return response()->json([
            'income' => $income,
            'expenses' => $expenses,
            'balance' => $income - $expenses
        ]);
    }

    private function checkForAlerts(Transaction $transaction)
    {
        // Logique pour vérifier les alertes (budget dépassé, etc.)
        // Sera implémentée dans le service dédié
    }
}
