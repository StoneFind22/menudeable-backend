<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('restaurant_id')
                ->constrained()
                ->onDelete('cascade');
                
            $table->string('name');
            $table->string('color', 7)->nullable(); // Hex code #FFFFFF
            $table->string('icon')->nullable(); // Emoji o clase de icono
            
            $table->timestamps();
            
            // Un tag no debe repetirse en un mismo restaurante
            $table->unique(['restaurant_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};