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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->comment('Nombre del producto');
            $table->text('description')->nullable()->comment('Descripción detallada del producto');
            $table->decimal('unit_price', 10, 2)->comment('Precio unitario del producto');
            $table->integer('stock')->default(0)->comment('Cantidad disponible en inventario');
            $table->boolean('is_active')->default(true)->comment('Estado activo/inactivo del producto');
            $table->timestamps();
            
            // Índices para mejorar el rendimiento
            $table->index('name');
            $table->index('is_active');
            $table->index('stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
