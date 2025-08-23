<?php

namespace App\Repositories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Repositorio para el manejo de proveedores del sistema
 */
class SupplierRepository
{
    /**
     * Obtiene todos los proveedores.
     */
    public function all(): Collection
    {
        return Supplier::with('purchases')->get();
    }

    /**
     * Obtiene todos los proveedores con paginación.
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Supplier::with('purchases')
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Busca proveedores por criterios específicos.
     */
    public function search(array $criteria): Collection
    {
        $query = Supplier::query();

        if (isset($criteria['name'])) {
            $query->where('name', 'like', '%' . $criteria['name'] . '%');
        }

        if (isset($criteria['email'])) {
            $query->where('email', 'like', '%' . $criteria['email'] . '%');
        }

        if (isset($criteria['phone'])) {
            $query->where('phone', 'like', '%' . $criteria['phone'] . '%');
        }

        if (isset($criteria['has_purchases'])) {
            if ($criteria['has_purchases']) {
                $query->whereHas('purchases');
            } else {
                $query->whereDoesntHave('purchases');
            }
        }

        return $query->with('purchases')->get();
    }

    /**
     * Obtiene un proveedor por su ID.
     */
    public function find(int $id): ?Supplier
    {
        return Supplier::with('purchases')->find($id);
    }

    /**
     * Obtiene un proveedor por su ID o lanza una excepción.
     */
    public function findOrFail(int $id): Supplier
    {
        return Supplier::with('purchases')->findOrFail($id);
    }

    /**
     * Crea un nuevo proveedor.
     */
    public function create(array $data): Supplier
    {
        return Supplier::create($data);
    }

    /**
     * Actualiza un proveedor existente.
     */
    public function update(int $id, array $data): bool
    {
        $supplier = $this->find($id);
        
        if (!$supplier) {
            return false;
        }

        return $supplier->update($data);
    }

    /**
     * Elimina un proveedor.
     */
    public function delete(int $id): bool
    {
        $supplier = $this->find($id);
        
        if (!$supplier) {
            return false;
        }

        // Verificar si el proveedor tiene compras asociadas
        if ($supplier->purchases()->exists()) {
            return false; // No se puede eliminar si tiene historial de compras
        }

        return $supplier->delete();
    }

    /**
     * Obtiene proveedores activos.
     */
    public function getActiveSuppliers(): Collection
    {
        return Supplier::active()->get();
    }

    /**
     * Obtiene proveedores con compras en un rango de fechas.
     */
    public function getSuppliersWithPurchasesInDateRange(string $startDate, string $endDate): Collection
    {
        return Supplier::whereHas('purchases', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('purchase_date', [$startDate, $endDate]);
        })->with('purchases')->get();
    }

    /**
     * Obtiene el proveedor con más compras.
     */
    public function getTopSupplier(): ?Supplier
    {
        return Supplier::withCount('purchases')
            ->orderBy('purchases_count', 'desc')
            ->first();
    }

    /**
     * Obtiene proveedores ordenados por monto total de compras.
     */
    public function getSuppliersByTotalAmount(): Collection
    {
        return Supplier::withSum('purchases', 'total_amount')
            ->orderBy('purchases_sum_total_amount', 'desc')
            ->get();
    }

    /**
     * Obtiene proveedores sin compras.
     */
    public function getSuppliersWithoutPurchases(): Collection
    {
        return Supplier::whereDoesntHave('purchases')->get();
    }

    /**
     * Cambia el estado activo/inactivo de un proveedor.
     */
    public function toggleStatus(int $id): bool
    {
        $supplier = $this->find($id);
        
        if (!$supplier) {
            return false;
        }

        $supplier->is_active = !$supplier->is_active;
        return $supplier->save();
    }

    /**
     * Obtiene estadísticas de proveedores.
     */
    public function getSupplierStats(): array
    {
        return [
            'total_suppliers' => Supplier::count(),
            'active_suppliers' => Supplier::active()->count(),
            'suppliers_with_purchases' => Supplier::whereHas('purchases')->count(),
            'suppliers_without_purchases' => Supplier::whereDoesntHave('purchases')->count(),
            'top_supplier' => $this->getTopSupplier()?->name ?? 'N/A',
        ];
    }

    /**
     * Obtiene proveedores por rango de compras.
     */
    public function getSuppliersByPurchaseRange(int $minPurchases, int $maxPurchases): Collection
    {
        return Supplier::withCount('purchases')
            ->havingBetween('purchases_count', [$minPurchases, $maxPurchases])
            ->get();
    }

    /**
     * Obtiene proveedores con compras recientes (últimos 30 días).
     */
    public function getSuppliersWithRecentPurchases(): Collection
    {
        $thirtyDaysAgo = now()->subDays(30)->toDateString();
        
        return Supplier::whereHas('purchases', function ($query) use ($thirtyDaysAgo) {
            $query->where('purchase_date', '>=', $thirtyDaysAgo);
        })->with('purchases')->get();
    }
} 