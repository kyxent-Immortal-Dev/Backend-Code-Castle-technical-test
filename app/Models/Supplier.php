<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Supplier - Representa los proveedores del sistema
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property string|null $address
 * @property bool $is_active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Supplier extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'is_active',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
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
     * Obtiene las compras asociadas a este proveedor.
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Scope para proveedores activos.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Obtiene el total de compras realizadas a este proveedor.
     */
    public function getTotalPurchases(): int
    {
        return $this->purchases()->count();
    }

    /**
     * Obtiene el monto total de compras realizadas a este proveedor.
     */
    public function getTotalAmount(): float
    {
        return (float) $this->purchases()->sum('total_amount');
    }

    /**
     * Obtiene la última compra realizada a este proveedor.
     */
    public function getLastPurchase()
    {
        return $this->purchases()->latest('purchase_date')->first();
    }

    /**
     * Verifica si el proveedor tiene compras registradas.
     */
    public function hasPurchases(): bool
    {
        return $this->purchases()->exists();
    }

    /**
     * Formatea el teléfono para mostrar.
     */
    public function getFormattedPhoneAttribute(): string
    {
        if (!$this->phone) {
            return 'No especificado';
        }
        
        // Formato básico para teléfonos
        $phone = preg_replace('/[^0-9]/', '', $this->phone);
        
        if (strlen($phone) === 10) {
            return '(' . substr($phone, 0, 3) . ') ' . substr($phone, 3, 3) . '-' . substr($phone, 6);
        }
        
        return $this->phone;
    }

    /**
     * Obtiene información resumida del proveedor.
     */
    public function getSummaryAttribute(): array
    {
        return [
            'total_purchases' => $this->getTotalPurchases(),
            'total_amount' => $this->getTotalAmount(),
            'last_purchase' => $this->getLastPurchase()?->purchase_date?->format('d/m/Y'),
            'status' => $this->is_active ? 'Activo' : 'Inactivo',
        ];
    }
}
