<?php

namespace App\Http\Controllers\Api;

use App\Models\Invoice;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class InvoiceController extends Controller
{
    /**
     * Afficher toutes les factures
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['company', 'user', 'transactions']);

        // Filtres
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->has('date_from')) {
            $query->where('issue_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('issue_date', '<=', $request->date_to);
        }

        if ($request->has('search')) {
            $query->where('invoice_number', 'like', '%' . $request->search . '%');
        }

        // Tri
        $orderBy = $request->get('order_by', 'created_at');
        $orderDir = $request->get('order_dir', 'desc');
        $query->orderBy($orderBy, $orderDir);

        $invoices = $query->paginate($request->get('per_page', 15));

        // Calculer les résumés
        $summary = [
            'total_amount' => $query->sum('amount'),
            'paid_amount' => Invoice::where('status', 'paid')->sum('amount'),
            'pending_amount' => Invoice::whereIn('status', ['sent', 'draft'])->sum('amount'),
            'overdue_amount' => Invoice::where('status', 'overdue')->sum('amount')
        ];

        return response()->json([
            'success' => true,
            'data' => $invoices,
            'summary' => $summary
        ]);
    }

    /**
     * Afficher une facture spécifique
     */
    public function show($id)
    {
        $invoice = Invoice::with(['company', 'user', 'transactions'])->find($id);

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Facture non trouvée'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $invoice
        ]);
    }

    /**
     * Créer une nouvelle facture
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'invoice_number' => 'required|string|max:255|unique:invoices',
            'amount' => 'required|numeric|min:0',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'status' => 'required|in:draft,sent,paid,overdue,cancelled',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $data = array_merge($request->all(), [
            'company_id' => $user->company_id,
            'user_id' => $user->id,
        ]);

        $invoice = Invoice::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Facture créée avec succès',
            'data' => $invoice->load(['company', 'user'])
        ], 201);
    }

    /**
     * Mettre à jour une facture
     */
    public function update(Request $request, $id)
    {
        $invoice = Invoice::find($id);

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Facture non trouvée'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'invoice_number' => 'sometimes|string|max:255|unique:invoices,invoice_number,' . $id,
            'company_id' => 'sometimes|exists:companies,id',
            'user_id' => 'sometimes|exists:users,id',
            'amount' => 'sometimes|numeric|min:0',
            'issue_date' => 'sometimes|date',
            'due_date' => 'sometimes|date|after_or_equal:issue_date',
            'status' => 'sometimes|in:draft,sent,paid,overdue,cancelled',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $invoice->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Facture mise à jour avec succès',
            'data' => $invoice->load(['company', 'user'])
        ]);
    }

    /**
     * Marquer une facture comme payée
     */
    public function markAsPaid($id)
    {
        $invoice = Invoice::find($id);

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Facture non trouvée'
            ], 404);
        }

        $invoice->status = 'paid';
        $invoice->save();

        return response()->json([
            'success' => true,
            'message' => 'Facture marquée comme payée',
            'data' => $invoice
        ]);
    }

    /**
     * Supprimer une facture
     */
    public function destroy($id)
    {
        $invoice = Invoice::find($id);

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Facture non trouvée'
            ], 404);
        }

        // Vérifier si des transactions sont associées
        if ($invoice->transactions()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer cette facture car des transactions y sont associées'
            ], 400);
        }

        $invoice->delete();

        return response()->json([
            'success' => true,
            'message' => 'Facture supprimée avec succès'
        ]);
    }

    /**
     * Obtenir les statistiques des factures
     */
    public function statistics()
    {
        $stats = [
            'total' => Invoice::count(),
            'total_amount' => Invoice::sum('amount'),
            'by_status' => [
                'draft' => Invoice::where('status', 'draft')->count(),
                'sent' => Invoice::where('status', 'sent')->count(),
                'paid' => Invoice::where('status', 'paid')->count(),
                'overdue' => Invoice::where('status', 'overdue')->count(),
                'cancelled' => Invoice::where('status', 'cancelled')->count()
            ],
            'amount_by_status' => [
                'draft' => Invoice::where('status', 'draft')->sum('amount'),
                'sent' => Invoice::where('status', 'sent')->sum('amount'),
                'paid' => Invoice::where('status', 'paid')->sum('amount'),
                'overdue' => Invoice::where('status', 'overdue')->sum('amount'),
                'cancelled' => Invoice::where('status', 'cancelled')->sum('amount')
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
