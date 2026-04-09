<?php

namespace App\Http\Controllers\Api;

use App\Models\Transaction;
use App\Models\Debt;
use App\Models\Alert;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{

    public function index(Request $request)
    {
        $companyId = $request->user()->company_id;

        // Trésorerie actuelle
        $cashBalance = $this->getCurrentCashBalance($companyId);

        // Total des revenus du mois
        $monthlyIncome = Transaction::where('company_id', $companyId)
            ->where('type', 'income')
            ->whereMonth('transaction_date', now()->month)
            ->sum('amount');

        // Total des dépenses du mois
        $monthlyExpenses = Transaction::where('company_id', $companyId)
            ->where('type', 'expense')
            ->whereMonth('transaction_date', now()->month)
            ->sum('amount');

        // Dettes à recevoir (clients)
        $receivables = Debt::where('company_id', $companyId)
            ->where('type', 'receivable')
            ->whereIn('status', ['pending', 'partial'])
            ->sum('remaining_amount');

        // Dettes à payer (fournisseurs)
        $payables = Debt::where('company_id', $companyId)
            ->where('type', 'payable')
            ->whereIn('status', ['pending', 'partial'])
            ->sum('remaining_amount');

        // Alertes non lues
        $unreadAlerts = Alert::where('company_id', $companyId)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'cash_balance' => $cashBalance,
                'monthly_income' => $monthlyIncome,
                'monthly_expenses' => $monthlyExpenses,
                'monthly_profit' => $monthlyIncome - $monthlyExpenses,
                'receivables' => $receivables,
                'payables' => $payables,
                'unread_alerts' => $unreadAlerts
            ]
        ]);
    }

    public function cashFlow(Request $request)
    {
        $companyId = $request->user()->company_id;

        // Flux de trésorerie des 6 derniers mois
        $cashFlow = collect(range(5, 0))->map(function ($monthsAgo) use ($companyId) {
            $date = now()->subMonths($monthsAgo);

            $income = Transaction::where('company_id', $companyId)
                ->where('type', 'income')
                ->whereMonth('transaction_date', $date->month)
                ->whereYear('transaction_date', $date->year)
                ->sum('amount');

            $expenses = Transaction::where('company_id', $companyId)
                ->where('type', 'expense')
                ->whereMonth('transaction_date', $date->month)
                ->whereYear('transaction_date', $date->year)
                ->sum('amount');

            return [
                'month' => $date->translatedFormat('F Y'),
                'income' => $income,
                'expenses' => $expenses,
                'balance' => $income - $expenses
            ];
        })->reverse()->values();

        return response()->json([
            'success' => true,
            'data' => $cashFlow
        ]);
    }

    public function expensesByCategory(Request $request)
    {
        $companyId = $request->user()->company_id;

        $expensesByCategory = Transaction::where('company_id', $companyId)
            ->where('type', 'expense')
            ->whereMonth('transaction_date', now()->month)
            ->with('category')
            ->get()
            ->groupBy('category.name')
            ->map(function ($transactions) {
                return $transactions->sum('amount');
            });

        return response()->json([
            'success' => true,
            'data' => $expensesByCategory
        ]);
    }

    public function recentTransactions(Request $request)
    {
        $companyId = $request->user()->company_id;

        $transactions = Transaction::where('company_id', $companyId)
            ->with('category')
            ->latest('transaction_date')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $transactions
        ]);
    }

    public function spendingTips(Request $request)
    {
        $companyId = $request->user()->company_id;

        // Conseils simples basés sur les dépenses du mois
        $monthlyExpenses = Transaction::where('company_id', $companyId)
            ->where('type', 'expense')
            ->whereMonth('transaction_date', now()->month)
            ->sum('amount');

        $tips = [];
        if ($monthlyExpenses > 500000) {
            $tips[] = 'Vos dépenses sont élevées ce mois-ci. Vérifiez vos catégories principales.';
        }

        return response()->json([
            'success' => true,
            'data' => $tips
        ]);
    }

    public function cashFlowPrediction(Request $request)
    {
        $companyId = $request->user()->company_id;

        // Simple prédiction basée sur la moyenne des 3 derniers mois
        $prediction = collect(range(0, 2))->map(function ($monthsAhead) use ($companyId) {
            $date = now()->addMonths($monthsAhead);

            // Moyenne des revenus
            $avgIncome = 50000; // Valeur par défaut
            // Moyenne des dépenses
            $avgExpenses = 30000; // Valeur par défaut

            return [
                'month' => $date->translatedFormat('F Y'),
                'predicted_income' => $avgIncome,
                'predicted_expenses' => $avgExpenses,
                'predicted_balance' => $avgIncome - $avgExpenses
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $prediction
        ]);
    }

    public function unpaidClients(Request $request)
    {
        $companyId = $request->user()->company_id;

        $unpaidClients = Debt::where('company_id', $companyId)
            ->where('type', 'receivable')
            ->where('status', 'pending')
            ->where('due_date', '<', now())
            ->orderBy('due_date')
            ->get();

        return response()->json($unpaidClients);
    }

    private function getCurrentCashBalance($companyId)
    {
        $totalIncome = Transaction::where('company_id', $companyId)
            ->where('type', 'income')
            ->sum('amount');

        $totalExpenses = Transaction::where('company_id', $companyId)
            ->where('type', 'expense')
            ->sum('amount');

        return $totalIncome - $totalExpenses;
    }
}
