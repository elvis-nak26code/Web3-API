<?php

namespace App\Http\Controllers\Api;

use App\Models\Report;
use App\Models\Transaction;
use App\Models\Invoice;
use App\Models\Debt;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    /**
     * Afficher tous les rapports
     */
    public function index(Request $request)
    {
        $query = Report::with('user');

        // Filtres
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('date_from')) {
            $query->where('generated_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('generated_date', '<=', $request->date_to);
        }

        $reports = $query->orderBy('created_at', 'desc')
                         ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $reports
        ]);
    }

    /**
     * Afficher un rapport spécifique
     */
    public function show($id)
    {
        $report = Report::with('user')->find($id);

        if (!$report) {
            return response()->json([
                'success' => false,
                'message' => 'Rapport non trouvé'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $report
        ]);
    }

    /**
     * Générer un nouveau rapport
     */
    public function generate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'type' => 'required|string|in:financial,activity,debt,invoice',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'format' => 'sometimes|string|in:pdf,excel,csv',
            'user_id' => 'required|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Générer les données du rapport selon le type
        $data = $this->generateReportData(
            $request->type,
            $request->date_from,
            $request->date_to
        );

        // Créer le rapport en base de données
        $report = Report::create([
            'title' => $request->title,
            'type' => $request->type,
            'data' => json_encode($data),
            'user_id' => $request->user_id,
            'generated_date' => now(),
            'format' => $request->get('format', 'pdf')
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Rapport généré avec succès',
            'data' => $report
        ], 201);
    }

    /**
     * Générer les données du rapport selon le type
     */
    private function generateReportData($type, $dateFrom, $dateTo)
    {
        switch ($type) {
            case 'financial':
                return [
                    'transactions' => Transaction::whereBetween('transaction_date', [$dateFrom, $dateTo])
                        ->with('category')
                        ->get(),
                    'summary' => [
                        'total_income' => Transaction::where('type', 'income')
                            ->whereBetween('transaction_date', [$dateFrom, $dateTo])
                            ->sum('amount'),
                        'total_expense' => Transaction::where('type', 'expense')
                            ->whereBetween('transaction_date', [$dateFrom, $dateTo])
                            ->sum('amount'),
                        'balance' => Transaction::whereBetween('transaction_date', [$dateFrom, $dateTo])
                            ->selectRaw('SUM(CASE WHEN type = "income" THEN amount ELSE -amount END) as balance')
                            ->value('balance') ?? 0
                    ]
                ];

            case 'activity':
                return [
                    'invoices' => Invoice::whereBetween('issue_date', [$dateFrom, $dateTo])->count(),
                    'invoices_amount' => Invoice::whereBetween('issue_date', [$dateFrom, $dateTo])->sum('amount'),
                    'debts' => Debt::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                    'debts_amount' => Debt::whereBetween('created_at', [$dateFrom, $dateTo])->sum('amount')
                ];

            case 'debt':
                return [
                    'debts' => Debt::with(['company', 'user'])
                        ->whereBetween('due_date', [$dateFrom, $dateTo])
                        ->get(),
                    'summary' => [
                        'total' => Debt::whereBetween('due_date', [$dateFrom, $dateTo])->sum('amount'),
                        'pending' => Debt::whereBetween('due_date', [$dateFrom, $dateTo])
                            ->where('status', 'pending')
                            ->sum('amount'),
                        'overdue' => Debt::whereBetween('due_date', [$dateFrom, $dateTo])
                            ->where('status', 'overdue')
                            ->sum('amount')
                    ]
                ];

            case 'invoice':
                return [
                    'invoices' => Invoice::with(['company', 'user'])
                        ->whereBetween('issue_date', [$dateFrom, $dateTo])
                        ->get(),
                    'summary' => [
                        'total' => Invoice::whereBetween('issue_date', [$dateFrom, $dateTo])->sum('amount'),
                        'paid' => Invoice::whereBetween('issue_date', [$dateFrom, $dateTo])
                            ->where('status', 'paid')
                            ->sum('amount'),
                        'unpaid' => Invoice::whereBetween('issue_date', [$dateFrom, $dateTo])
                            ->whereIn('status', ['sent', 'overdue'])
                            ->sum('amount')
                    ]
                ];

            default:
                return [];
        }
    }

    /**
     * Télécharger un rapport
     */
    public function download($id)
    {
        $report = Report::find($id);

        if (!$report) {
            return response()->json([
                'success' => false,
                'message' => 'Rapport non trouvé'
            ], 404);
        }

        // Simuler la génération d'un fichier
        $filename = 'report_' . $report->id . '_' . date('Y-m-d') . '.pdf';
        $content = json_encode($report->data, JSON_PRETTY_PRINT);

        // Sauvegarder temporairement
        Storage::disk('local')->put('temp/' . $filename, $content);

        return response()->download(storage_path('app/temp/' . $filename))->deleteFileAfterSend(true);
    }

    /**
     * Supprimer un rapport
     */
    public function destroy($id)
    {
        $report = Report::find($id);

        if (!$report) {
            return response()->json([
                'success' => false,
                'message' => 'Rapport non trouvé'
            ], 404);
        }

        $report->delete();

        return response()->json([
            'success' => true,
            'message' => 'Rapport supprimé avec succès'
        ]);
    }
}