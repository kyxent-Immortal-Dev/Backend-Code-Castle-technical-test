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
        Schema::create('purchase_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->onDelete('cascade')->comment('ID de la compra');
            $table->foreignId('product_id')->constrained()->onDelete('restrict')->comment('ID del producto');
            $table->integer('quantity')->comment('Cantidad comprada del producto');
            $table->decimal('purchase_price', 10, 2)->comment('Precio de compra unitario');
            $table->decimal('subtotal', 12, 2)->comment('Subtotal de la línea (cantidad * precio)');
            $table->timestamps();
            
            // Índices para mejorar el rendimiento
            $table->index('purchase_id');
            $table->index('product_id');
            
            // Índice compuesto para evitar duplicados
            $table->unique(['purchase_id', 'product_id'], 'unique_purchase_product');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_details');
    }
};
