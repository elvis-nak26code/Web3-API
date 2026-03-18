<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    AuthController,
    DashboardController,
    TransactionController,
    CategoryController,
    DebtController,
    InvoiceController,
    ReportController,
    AlertController
};

// Routes publiques
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Routes protégées (nécessitent authentification)
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/cash-flow', [DashboardController::class, 'cashFlow']);
    Route::get('/dashboard/expenses-by-category', [DashboardController::class, 'expensesByCategory']);
    Route::get('/dashboard/recent-transactions', [DashboardController::class, 'recentTransactions']);

    // Transactions
    Route::apiResource('transactions', TransactionController::class);
    Route::post('/transactions/{transaction}/receipt', [TransactionController::class, 'uploadReceipt']);
    Route::get('/transactions/stats/summary', [TransactionController::class, 'summary']);

    // Categories
    Route::apiResource('categories', CategoryController::class);
    Route::get('/categories/{category}/budget-status', [CategoryController::class, 'budgetStatus']);

    // Dettes
    Route::apiResource('debts', DebtController::class);
    Route::post('/debts/{debt}/payments', [DebtController::class, 'addPayment']);
    Route::get('/debts/{debt}/payment-history', [DebtController::class, 'paymentHistory']);
    Route::post('/debts/{debt}/send-reminder', [DebtController::class, 'sendReminder']);

    // Factures
    Route::apiResource('invoices', InvoiceController::class);
    Route::post('/invoices/{invoice}/upload', [InvoiceController::class, 'uploadFile']);
    Route::post('/invoices/{invoice}/mark-as-paid', [InvoiceController::class, 'markAsPaid']);
    Route::get('/invoices/upcoming/due', [InvoiceController::class, 'upcomingDue']);

    // Rapports
    Route::prefix('reports')->group(function () {
        Route::get('/daily', [ReportController::class, 'daily']);
        Route::get('/monthly', [ReportController::class, 'monthly']);
        Route::get('/yearly', [ReportController::class, 'yearly']);
        Route::get('/custom', [ReportController::class, 'custom']);
        Route::get('/export/pdf', [ReportController::class, 'exportPdf']);
        Route::get('/export/excel', [ReportController::class, 'exportExcel']);
    });

    // Alertes
    Route::prefix('alerts')->group(function () {
        Route::get('/', [AlertController::class, 'index']);
        Route::patch('/{alert}/read', [AlertController::class, 'markAsRead']);
        Route::post('/mark-all-read', [AlertController::class, 'markAllAsRead']);
        Route::delete('/{alert}', [AlertController::class, 'destroy']);
    });

    // Analyses intelligentes
    Route::prefix('insights')->group(function () {
        Route::get('/spending-tips', [DashboardController::class, 'spendingTips']);
        Route::get('/cash-flow-prediction', [DashboardController::class, 'cashFlowPrediction']);
        Route::get('/unpaid-clients', [DashboardController::class, 'unpaidClients']);
    });
});