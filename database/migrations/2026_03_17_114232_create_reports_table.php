<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type'); // financial, activity, debt, invoice, etc.
            $table->json('data')->nullable(); // Pour stocker les données du rapport en JSON
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('generated_date');
            $table->string('format')->default('pdf'); // pdf, excel, csv
            $table->text('description')->nullable();
            $table->string('file_path')->nullable(); // Pour stocker le chemin du fichier généré
            $table->timestamps();

            // Index pour améliorer les performances
            $table->index('type');
            $table->index('user_id');
            $table->index('generated_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};