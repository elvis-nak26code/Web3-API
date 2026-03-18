<?php

namespace App\Http\Controllers\Api;

use App\Models\Debt;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class DebtController extends Controller
{
    /**
     * Afficher toutes les dettes
     */
    public function index(Request $request)
    {
        $query = Debt::with(['company', 'user']);

        // Filtres
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('due_date_from')) {
            $query->where('due_date', '>=', $request->due_date_from);
        }

        if ($request->has('due_date_to')) {
            $query->where('due_date', '<=', $request->due_date_to);
        }

        if ($request->has('overdue') && $request->overdue == 'true') {
            $query->where('due_date', '<', now())
                  ->where('status', '!=', 'paid');
        }

        // Tri
        $orderBy = $request->get('order_by', 'due_date');
        $orderDir = $request->get('order_dir', 'asc');
        $query->orderBy($orderBy, $orderDir);

        $debts = $query->paginate($request->get('per_page', 15));

        // Calculer les résumés
        $summary = [
            'total_debts' => $query->sum('amount'),
            'total_remaining' => $query->sum('remaining_amount'),
            'overdue_count' => Debt::where('due_date', '<', now())
                                   ->where('status', '!=', 'paid')
                                   ->count()
        ];

        return response()->json([
            'success' => true,
            'data' => $debts,
            'summary' => $summary
        ]);
    }

    /**
     * Afficher une dette spécifique
     */
    public function show($id)
    {
        $debt = Debt::with(['company', 'user'])->find($id);

        if (!$debt) {
            return response()->json([
                'success' => false,
                'message' => 'Dette non trouvée'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $debt
        ]);
    }

    /**
     * Créer une nouvelle dette
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'remaining_amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'company_id' => 'nullable|exists:companies,id',
            'user_id' => 'required|exists:users,id',
            'status' => 'required|in:pending,paid,overdue',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Ajuster automatiquement le statut si la date est dépassée
        $data = $request->all();
        if (strtotime($data['due_date']) < time() && $data['status'] == 'pending') {
            $data['status'] = 'overdue';
        }

        $debt = Debt::create($data);

        // Créer une alerte si la dette est en retard
        if ($debt->status == 'overdue') {
            // Vous pouvez appeler un service d'alerte ici
        }

        return response()->json([
            'success' => true,
            'message' => 'Dette créée avec succès',
            'data' => $debt->load(['company', 'user'])
        ], 201);
    }

    /**
     * Mettre à jour une dette
     */
    public function update(Request $request, $id)
    {
        $debt = Debt::find($id);

        if (!$debt) {
            return response()->json([
                'success' => false,
                'message' => 'Dette non trouvée'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'amount' => 'sometimes|numeric|min:0',
            'remaining_amount' => 'sometimes|numeric|min:0',
            'due_date' => 'sometimes|date',
            'company_id' => 'nullable|exists:companies,id',
            'user_id' => 'sometimes|exists:users,id',
            'status' => 'sometimes|in:pending,paid,overdue',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Vérifier automatiquement le statut
        $data = $request->all();
        if (isset($data['due_date']) && strtotime($data['due_date']) < time()) {
            if (!isset($data['status']) || $data['status'] == 'pending') {
                $data['status'] = 'overdue';
            }
        }

        $debt->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Dette mise à jour avec succès',
            'data' => $debt->load(['company', 'user'])
        ]);
    }

    /**
     * Effectuer un paiement sur une dette
     */
    public function makePayment(Request $request, $id)
    {
        $debt = Debt::find($id);

        if (!$debt) {
            return response()->json([
                'success' => false,
                'message' => 'Dette non trouvée'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $paymentAmount = $request->amount;

        if ($paymentAmount > $debt->remaining_amount) {
            return response()->json([
                'success' => false,
                'message' => 'Le montant du paiement dépasse le montant restant'
            ], 400);
        }

        $debt->remaining_amount -= $paymentAmount;

        if ($debt->remaining_amount == 0) {
            $debt->status = 'paid';
        }

        $debt->save();

        // Créer une transaction pour ce paiement
        // Logique de création de transaction à ajouter

        return response()->json([
            'success' => true,
            'message' => 'Paiement enregistré avec succès',
            'data' => $debt
        ]);
    }

    /**
     * Supprimer une dette
     */
    public function destroy($id)
    {
        $debt = Debt::find($id);

        if (!$debt) {
            return response()->json([
                'success' => false,
                'message' => 'Dette non trouvée'
            ], 404);
        }

        $debt->delete();

        return response()->json([
            'success' => true,
            'message' => 'Dette supprimée avec succès'
        ]);
    }

    /**
     * Obtenir les statistiques des dettes
     */
    public function statistics()
    {
        $stats = [
            'total' => Debt::sum('amount'),
            'total_remaining' => Debt::sum('remaining_amount'),
            'paid' => Debt::where('status', 'paid')->sum('amount'),
            'pending' => Debt::where('status', 'pending')->sum('amount'),
            'overdue' => Debt::where('status', 'overdue')->sum('amount'),
            'count_by_status' => [
                'paid' => Debt::where('status', 'paid')->count(),
                'pending' => Debt::where('status', 'pending')->count(),
                'overdue' => Debt::where('status', 'overdue')->count()
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}