<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'user_id',
        'sale_date',
        'total_amount',
        'status',
        'notes'
    ];

    protected $casts = [
        'sale_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Obtiene el cliente de la venta.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Obtiene el usuario que realizó la venta.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtiene los detalles de la venta.
     */
    public function saleDetails(): HasMany
    {
        return $this->hasMany(SaleDetail::class);
    }

    /**
     * Scope para ventas activas.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope para ventas canceladas.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }
}