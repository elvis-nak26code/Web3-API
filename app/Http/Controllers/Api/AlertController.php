<?php

namespace App\Http\Controllers\Api;

use App\Models\Alert;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class AlertController extends Controller
{
    /**
     * Afficher toutes les alertes
     */
    public function index(Request $request)
    {
        $query = Alert::with('user');

        // If authenticated, filter by company
        if ($request->user()) {
            $query->where('company_id', $request->user()->company_id);
        }

        // Filtres
        if ($request->has('is_read')) {
            $query->where('is_read', $request->is_read === 'true');
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Tri
        $orderBy = $request->get('order_by', 'created_at');
        $orderDir = $request->get('order_dir', 'desc');
        $query->orderBy($orderBy, $orderDir);

        $alerts = $query->get();

        // Compter les non lues
        $unreadCountQuery = Alert::where('is_read', false);
        if ($request->user()) {
            $unreadCountQuery->where('company_id', $request->user()->company_id);
        }
        if ($request->has('user_id')) {
            $unreadCountQuery->where('user_id', $request->user_id);
        }
        $unreadCount = $unreadCountQuery->count();

        return response()->json([
            'success' => true,
            'data' => $alerts,
            'unread_count' => $unreadCount
        ]);
    }

    /**
     * Afficher une alerte spécifique
     */
    public function show(Request $request, $id)
    {
        $alert = Alert::with('user')
            ->where('company_id', $request->user()->company_id)
            ->find($id);

        if (!$alert) {
            return response()->json([
                'success' => false,
                'message' => 'Alerte non trouvée'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $alert
        ]);
    }

    /**
     * Créer une nouvelle alerte
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|string|in:info,warning,danger,success',
            'user_id' => 'required|exists:users,id',
            'is_read' => 'boolean',
            'link' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $alert = Alert::create(array_merge($request->all(), [
            'company_id' => $request->user()->company_id
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Alerte créée avec succès',
            'data' => $alert->load('user')
        ], 201);
    }

    /**
     * Mettre à jour une alerte
     */
    public function update(Request $request, $id)
    {
        $alert = Alert::where('company_id', $request->user()->company_id)->find($id);

        if (!$alert) {
            return response()->json([
                'success' => false,
                'message' => 'Alerte non trouvée'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'message' => 'sometimes|string',
            'type' => 'sometimes|string|in:info,warning,danger,success',
            'is_read' => 'sometimes|boolean',
            'link' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $alert->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Alerte mise à jour avec succès',
            'data' => $alert->load('user')
        ]);
    }

    /**
     * Marquer une alerte comme lue
     */
    public function markAsRead(Request $request, $id)
    {
        $alert = Alert::where('company_id', $request->user()->company_id)->find($id);

        if (!$alert) {
            return response()->json([
                'success' => false,
                'message' => 'Alerte non trouvée'
            ], 404);
        }

        $alert->is_read = true;
        $alert->read_at = now();
        $alert->save();

        return response()->json([
            'success' => true,
            'message' => 'Alerte marquée comme lue',
            'data' => $alert
        ]);
    }

    /**
     * Marquer toutes les alertes d'un utilisateur comme lues
     */
    public function markAllAsRead(Request $request)
    {
        $updated = Alert::where('company_id', $request->user()->company_id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        return response()->json([
            'success' => true,
            'message' => $updated . ' alertes marquées comme lues',
            'updated_count' => $updated
        ]);
    }

    /**
     * Obtenir le nombre d'alertes non lues
     */
    public function getUnreadCount(Request $request)
    {
        $count = Alert::where('company_id', $request->user()->company_id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'unread_count' => $count
            ]
        ]);
    }

    /**
     * Supprimer une alerte
     */
    public function destroy(Request $request, $id)
    {
        $alert = Alert::where('company_id', $request->user()->company_id)->find($id);

        if (!$alert) {
            return response()->json([
                'success' => false,
                'message' => 'Alerte non trouvée'
            ], 404);
        }

        $alert->delete();

        return response()->json([
            'success' => true,
            'message' => 'Alerte supprimée avec succès'
        ]);
    }

    /**
     * Supprimer toutes les alertes lues d'un utilisateur
     */
    public function clearReadAlerts(Request $request)
    {
        $deleted = Alert::where('company_id', $request->user()->company_id)
            ->where('is_read', true)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => $deleted . ' alertes lues supprimées',
            'deleted_count' => $deleted
        ]);
    }
}
