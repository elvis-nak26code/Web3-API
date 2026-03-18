<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // Générer les alertes quotidiennement
        $schedule->command('alerts:generate')->daily();

        // Générer les rapports journaliers
        $schedule->command('reports:daily')->dailyAt('23:00');

        // Vérifier les factures en retard
        $schedule->command('invoices:check-overdue')->daily();

        // Envoyer les rappels de dettes
        $schedule->command('debts:send-reminders')->daily();
    }
}