<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('category_id')
                ->constrained()
                ->onDelete('cascade');
                
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            
            // Precio base (nullable porque si tiene variantes, el precio puede estar ahí)
            $table->decimal('base_price', 10, 2)->nullable();
            
            $table->boolean('is_available')->default(true);
            $table->boolean('has_variants')->default(false);
            
            // Ingredientes como JSON
            $table->json('ingredients_list')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};