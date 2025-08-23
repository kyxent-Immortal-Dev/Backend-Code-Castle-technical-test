<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Product - Representa los productos del inventario
 * 
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property float $unit_price
 * @property int $stock
 * @property bool $is_active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Product extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'description',
        'unit_price',
        'stock',
        'is_active',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'unit_price' => 'decimal:2',
        'stock' => 'integer',
        'is_active' => 'boolean',
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
     * Obtiene los detalles de compra asociados a este producto.
     */
    public function purchaseDetails(): HasMany
    {
        return $this->hasMany(PurchaseDetail::class);
    }

    /**
     * Scope para productos activos.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para productos con stock disponible.
     */
    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    /**
     * Scope para productos con stock bajo (menos de 10 unidades).
     */
    public function scopeLowStock($query)
    {
        return $query->where('stock', '<', 10);
    }

    /**
     * Verifica si el producto tiene stock disponible.
     */
    public function hasStock(int $quantity = 1): bool
    {
        return $this->stock >= $quantity;
    }

    /**
     * Aumenta el stock del producto.
     */
    public function increaseStock(int $quantity): bool
    {
        $this->stock += $quantity;
        return $this->save();
    }

    /**
     * Disminuye el stock del producto.
     */
    public function decreaseStock(int $quantity): bool
    {
        if ($this->stock < $quantity) {
            return false;
        }
        
        $this->stock -= $quantity;
        return $this->save();
    }

    /**
     * Obtiene el valor total del inventario para este producto.
     */
    public function getInventoryValue(): float
    {
        return (float) ($this->stock * $this->unit_price);
    }

    /**
     * Formatea el precio unitario para mostrar.
     */
    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->unit_price, 2);
    }

    /**
     * Formatea el stock para mostrar.
     */
    public function getStockStatusAttribute(): string
    {
        if ($this->stock <= 0) {
            return 'Sin stock';
        } elseif ($this->stock < 10) {
            return 'Stock bajo';
        } else {
            return 'Stock disponible';
        }
    }
}
