<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'sale_price',
        'subtotal'
    ];

    protected $casts = [
        'sale_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    /**
     * Obtiene la venta a la que pertenece este detalle.
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Obtiene el producto vendido.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calcula el subtotal automáticamente.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($saleDetail) {
            $saleDetail->subtotal = $saleDetail->quantity * $saleDetail->sale_price;
        });

        static::updating(function ($saleDetail) {
            $saleDetail->subtotal = $saleDetail->quantity * $saleDetail->sale_price;
        });
    }
}