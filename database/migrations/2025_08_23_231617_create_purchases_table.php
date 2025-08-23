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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->onDelete('restrict')->comment('ID del proveedor');
            $table->foreignId('user_id')->constrained()->onDelete('restrict')->comment('ID del usuario que registra la compra');
            $table->date('purchase_date')->comment('Fecha de la compra');
            $table->decimal('total_amount', 12, 2)->default(0)->comment('Monto total de la compra');
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending')->comment('Estado de la compra');
            $table->text('notes')->nullable()->comment('Notas adicionales de la compra');
            $table->timestamps();
            
            // Índices para mejorar el rendimiento
            $table->index('supplier_id');
            $table->index('user_id');
            $table->index('purchase_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
