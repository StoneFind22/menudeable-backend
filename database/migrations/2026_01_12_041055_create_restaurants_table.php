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
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            
            // Relación 1:1 con User (Cada usuario tiene un restaurante en el MVP)
            $table->foreignId('user_id')
                ->unique()
                ->constrained()
                ->onDelete('cascade');

            $table->string('name');
            $table->string('slug', 200)->unique();
            $table->string('country', 100);
            $table->string('city', 100);

            // Campos opcionales
            $table->string('logo_path')->nullable();
            $table->string('classification', 100)->nullable();
            $table->text('description')->nullable();
            $table->string('address')->nullable();
            $table->string('phone', 20)->nullable();
            
            // Ruta del QR generado automáticamente
            $table->string('qr_path')->nullable();

            $table->timestamps();

            // Índice para búsquedas rápidas por slug
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurants');
    }
};