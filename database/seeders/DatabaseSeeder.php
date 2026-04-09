<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Créer des catégories par défaut pour la company 1
        if (\App\Models\Company::find(1)) {
            \App\Models\Category::firstOrCreate([
                'company_id' => 1,
                'name' => 'Ventes'
            ], [
                'type' => 'income',
                'color' => '#34d399',
                'is_active' => true
            ]);

            \App\Models\Category::firstOrCreate([
                'company_id' => 1,
                'name' => 'Services'
            ], [
                'type' => 'income',
                'color' => '#6c8eff',
                'is_active' => true
            ]);

            \App\Models\Category::firstOrCreate([
                'company_id' => 1,
                'name' => 'Achats'
            ], [
                'type' => 'expense',
                'color' => '#f87171',
                'is_active' => true
            ]);

            \App\Models\Category::firstOrCreate([
                'company_id' => 1,
                'name' => 'Marketing'
            ], [
                'type' => 'expense',
                'color' => '#fbbf24',
                'is_active' => true
            ]);
        }
    }
}
