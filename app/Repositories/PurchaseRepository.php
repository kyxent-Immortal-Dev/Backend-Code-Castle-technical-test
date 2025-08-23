<?php

namespace App\Repositories;

use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Repositorio para el manejo de compras del sistema
 */
class PurchaseRepository
{
    /**
     * Obtiene todas las compras.
     */
    public function all(): Collection
    {
        return Purchase::with(['supplier', 'user', 'details.product'])->get();
    }

    /**
     * Obtiene todas las compras con paginación.
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Purchase::with(['supplier', 'user', 'details.product'])
            ->orderBy('purchase_date', 'desc')
            ->paginate($perPage);
    }

    /**
     * Busca compras por criterios específicos.
     */
    public function search(array $criteria): Collection
    {
        $query = Purchase::query();

        if (isset($criteria['supplier_id'])) {
            $query->where('supplier_id', $criteria['supplier_id']);
        }

        if (isset($criteria['user_id'])) {
            $query->where('user_id', $criteria['user_id']);
        }

        if (isset($criteria['status'])) {
            $query->where('status', $criteria['status']);
        }

        if (isset($criteria['start_date'])) {
            $query->where('purchase_date', '>=', $criteria['start_date']);
        }

        if (isset($criteria['end_date'])) {
            $query->where('purchase_date', '<=', $criteria['end_date']);
        }

        if (isset($criteria['min_amount'])) {
            $query->where('total_amount', '>=', $criteria['min_amount']);
        }

        if (isset($criteria['max_amount'])) {
            $query->where('total_amount', '<=', $criteria['max_amount']);
        }

        return $query->with(['supplier', 'user', 'details.product'])->get();
    }

    /**
     * Obtiene una compra por su ID.
     */
    public function find(int $id): ?Purchase
    {
        return Purchase::with(['supplier', 'user', 'details.product'])->find($id);
    }

    /**
     * Obtiene una compra por su ID o lanza una excepción.
     */
    public function findOrFail(int $id): Purchase
    {
        return Purchase::with(['supplier', 'user', 'details.product'])->findOrFail($id);
    }

    /**
     * Crea una nueva compra con sus detalles.
     */
    public function create(array $data): Purchase
    {
        return DB::transaction(function () use ($data) {
            // Crear la compra principal
            $purchase = Purchase::create([
                'supplier_id' => $data['supplier_id'],
                'user_id' => $data['user_id'],
                'purchase_date' => $data['purchase_date'],
                'notes' => $data['notes'] ?? null,
                'status' => Purchase::STATUS_PENDING,
                'total_amount' => 0, // Se calculará después
            ]);

            // Crear los detalles de la compra
            $totalAmount = 0;
            foreach ($data['details'] as $detailData) {
                $detail = PurchaseDetail::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $detailData['product_id'],
                    'quantity' => $detailData['quantity'],
                    'purchase_price' => $detailData['purchase_price'],
                ]);

                $totalAmount += $detail->subtotal;
            }

            // Actualizar el monto total de la compra
            $purchase->update(['total_amount' => $totalAmount]);

            return $purchase->load(['supplier', 'user', 'details.product']);
        });
    }

    /**
     * Actualiza una compra existente.
     */
    public function update(int $id, array $data): bool
    {
        $purchase = $this->find($id);
        
        if (!$purchase) {
            return false;
        }

        // Solo se pueden actualizar compras pendientes
        if (!$purchase->isPending()) {
            return false;
        }

        return $purchase->update($data);
    }

    /**
     * Elimina una compra.
     */
    public function delete(int $id): bool
    {
        $purchase = $this->find($id);
        
        if (!$purchase) {
            return false;
        }

        // Solo se pueden eliminar compras pendientes
        if (!$purchase->isPending()) {
            return false;
        }

        return DB::transaction(function () use ($purchase) {
            // Eliminar los detalles primero (cascade)
            $purchase->details()->delete();
            
            // Eliminar la compra
            return $purchase->delete();
        });
    }

    /**
     * Marca una compra como completada y actualiza el stock.
     */
    public function completePurchase(int $id): bool
    {
        $purchase = $this->find($id);
        
        if (!$purchase || !$purchase->isPending()) {
            return false;
        }

        return DB::transaction(function () use ($purchase) {
            // Actualizar el stock de cada producto
            foreach ($purchase->details as $detail) {
                $product = Product::find($detail->product_id);
                if ($product) {
                    $product->increaseStock($detail->quantity);
                }
            }

            // Marcar la compra como completada
            return $purchase->markAsCompleted();
        });
    }

    /**
     * Marca una compra como cancelada.
     */
    public function cancelPurchase(int $id): bool
    {
        $purchase = $this->find($id);
        
        if (!$purchase || !$purchase->isPending()) {
            return false;
        }

        return $purchase->markAsCancelled();
    }

    /**
     * Obtiene compras por estado.
     */
    public function getByStatus(string $status): Collection
    {
        return Purchase::where('status', $status)
            ->with(['supplier', 'user', 'details.product'])
            ->orderBy('purchase_date', 'desc')
            ->get();
    }

    /**
     * Obtiene compras en un rango de fechas.
     */
    public function getByDateRange(string $startDate, string $endDate): Collection
    {
        return Purchase::dateRange($startDate, $endDate)
            ->with(['supplier', 'user', 'details.product'])
            ->orderBy('purchase_date', 'desc')
            ->get();
    }

    /**
     * Obtiene compras por proveedor.
     */
    public function getBySupplier(int $supplierId): Collection
    {
        return Purchase::where('supplier_id', $supplierId)
            ->with(['supplier', 'user', 'details.product'])
            ->orderBy('purchase_date', 'desc')
            ->get();
    }

    /**
     * Obtiene compras por usuario.
     */
    public function getByUser(int $userId): Collection
    {
        return Purchase::where('user_id', $userId)
            ->with(['supplier', 'user', 'details.product'])
            ->orderBy('purchase_date', 'desc')
            ->get();
    }

    /**
     * Obtiene estadísticas de compras.
     */
    public function getPurchaseStats(): array
    {
        return [
            'total_purchases' => Purchase::count(),
            'pending_purchases' => Purchase::pending()->count(),
            'completed_purchases' => Purchase::completed()->count(),
            'cancelled_purchases' => Purchase::cancelled()->count(),
            'total_amount' => (float) Purchase::sum('total_amount'),
            'average_amount' => (float) Purchase::avg('total_amount'),
            'this_month_purchases' => Purchase::whereMonth('purchase_date', now()->month)->count(),
            'this_month_amount' => (float) Purchase::whereMonth('purchase_date', now()->month)->sum('total_amount'),
        ];
    }

    /**
     * Obtiene el total de compras por mes en el último año.
     */
    public function getMonthlyTotals(): array
    {
        $monthlyTotals = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $month = $date->format('M Y');
            
            $total = Purchase::whereYear('purchase_date', $date->year)
                ->whereMonth('purchase_date', $date->month)
                ->sum('total_amount');
            
            $monthlyTotals[$month] = (float) $total;
        }
        
        return $monthlyTotals;
    }

    /**
     * Obtiene productos más comprados.
     */
    public function getTopProducts(int $limit = 10): Collection
    {
        return PurchaseDetail::select('product_id')
            ->selectRaw('SUM(quantity) as total_quantity')
            ->selectRaw('SUM(subtotal) as total_amount')
            ->with('product')
            ->groupBy('product_id')
            ->orderBy('total_quantity', 'desc')
            ->limit($limit)
            ->get();
    }
} 