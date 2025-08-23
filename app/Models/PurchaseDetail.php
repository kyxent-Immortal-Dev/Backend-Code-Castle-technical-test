<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo PurchaseDetail - Representa los detalles de las compras
 * 
 * @property int $id
 * @property int $purchase_id
 * @property int $product_id
 * @property int $quantity
 * @property float $purchase_price
 * @property float $subtotal
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class PurchaseDetail extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<string>
     */
    protected $fillable = [
        'purchase_id',
        'product_id',
        'quantity',
        'purchase_price',
        'subtotal',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'integer',
        'purchase_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Los atributos que deben ser ocultos para arrays.
     *
     * @var array<string>
     */
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    /**
     * Obtiene la compra asociada a este detalle.
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * Obtiene el producto asociado a este detalle.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calcula el subtotal de la línea.
     */
    public function calculateSubtotal(): float
    {
        $this->subtotal = $this->quantity * $this->purchase_price;
        return (float) $this->subtotal;
    }

    /**
     * Actualiza el subtotal y guarda el modelo.
     */
    public function updateSubtotal(): bool
    {
        $this->subtotal = $this->calculateSubtotal();
        return $this->save();
    }

    /**
     * Formatea el precio de compra para mostrar.
     */
    public function getFormattedPurchasePriceAttribute(): string
    {
        return '$' . number_format($this->purchase_price, 2);
    }

    /**
     * Formatea el subtotal para mostrar.
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return '$' . number_format($this->subtotal, 2);
    }

    /**
     * Obtiene información resumida del detalle.
     */
    public function getSummaryAttribute(): array
    {
        return [
            'product_name' => $this->product->name ?? 'Producto no encontrado',
            'quantity' => $this->quantity,
            'purchase_price' => $this->getFormattedPurchasePriceAttribute(),
            'subtotal' => $this->getFormattedSubtotalAttribute(),
        ];
    }

    /**
     * Hook para calcular automáticamente el subtotal antes de guardar.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($detail) {
            $detail->calculateSubtotal();
        });

        static::updating(function ($detail) {
            $detail->calculateSubtotal();
        });
    }
}
