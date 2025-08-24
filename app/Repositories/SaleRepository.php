<?php

namespace App\Repositories;

use App\Models\Sale;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Repositorio para el manejo de ventas
 */
class SaleRepository
{
    /**
     * Obtiene todas las ventas.
     */
    public function all(): Collection
    {
        return Sale::with(['client', 'user', 'saleDetails.product'])->get();
    }

    /**
     * Obtiene todas las ventas con paginación.
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Sale::with(['client', 'user', 'saleDetails.product'])
            ->orderBy('sale_date', 'desc')
            ->paginate($perPage);
    }

    /**
     * Obtiene una venta por su ID.
     */
    public function find(int $id): ?Sale
    {
        return Sale::with(['client', 'user', 'saleDetails.product'])->find($id);
    }

    /**
     * Obtiene una venta por su ID o lanza una excepción.
     */
    public function findOrFail(int $id): Sale
    {
        return Sale::with(['client', 'user', 'saleDetails.product'])->findOrFail($id);
    }

    /**
     * Crea una nueva venta y descuenta el stock de los productos.
     */
    public function create(array $data): Sale
    {
        return DB::transaction(function () use ($data) {
            // Crear la venta
            $sale = Sale::create([
                'client_id' => $data['client_id'],
                'user_id' => $data['user_id'],
                'sale_date' => $data['sale_date'] ?? now(),
                'total_amount' => 0, // Se calculará después
                'status' => 'active',
                'notes' => $data['notes'] ?? null
            ]);

            $totalAmount = 0;

            // Procesar cada detalle de venta
            foreach ($data['sale_details'] as $detail) {
                $product = Product::findOrFail($detail['product_id']);
                
                // Verificar stock disponible
                if ($product->stock < $detail['quantity']) {
                    throw new \Exception("Stock insuficiente para el producto: {$product->name}");
                }

                // Crear el detalle de venta
                $saleDetail = $sale->saleDetails()->create([
                    'product_id' => $detail['product_id'],
                    'quantity' => $detail['quantity'],
                    'sale_price' => $detail['sale_price'],
                    'subtotal' => $detail['quantity'] * $detail['sale_price']
                ]);

                // Descontar stock del producto
                $product->decreaseStock($detail['quantity']);
                
                $totalAmount += $saleDetail->subtotal;
            }

            // Actualizar el monto total de la venta
            $sale->update(['total_amount' => $totalAmount]);

            return $sale->load(['client', 'user', 'saleDetails.product']);
        });
    }

    /**
     * Cancela una venta y restaura el stock de los productos.
     */
    public function cancel(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $sale = $this->findOrFail($id);
            
            if ($sale->status === 'cancelled') {
                return false; // Ya está cancelada
            }

            // Restaurar stock de cada producto
            foreach ($sale->saleDetails as $detail) {
                $product = $detail->product;
                $product->increaseStock($detail->quantity);
            }

            // Marcar venta como cancelada
            $sale->status = 'cancelled';
            return $sale->save();
        });
    }

    /**
     * Obtiene ventas por cliente.
     */
    public function getSalesByClient(int $clientId): Collection
    {
        return Sale::with(['client', 'user', 'saleDetails.product'])
            ->where('client_id', $clientId)
            ->orderBy('sale_date', 'desc')
            ->get();
    }

    /**
     * Obtiene ventas por usuario.
     */
    public function getSalesByUser(int $userId): Collection
    {
        return Sale::with(['client', 'user', 'saleDetails.product'])
            ->where('user_id', $userId)
            ->orderBy('sale_date', 'desc')
            ->get();
    }

    /**
     * Obtiene ventas por rango de fechas.
     */
    public function getSalesByDateRange(string $startDate, string $endDate): Collection
    {
        return Sale::with(['client', 'user', 'saleDetails.product'])
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->orderBy('sale_date', 'desc')
            ->get();
    }

    /**
     * Obtiene ventas por estado.
     */
    public function getSalesByStatus(string $status): Collection
    {
        return Sale::with(['client', 'user', 'saleDetails.product'])
            ->where('status', $status)
            ->orderBy('sale_date', 'desc')
            ->get();
    }

    /**
     * Obtiene estadísticas de ventas.
     */
    public function getSalesStats(): array
    {
        return [
            'total_sales' => Sale::count(),
            'active_sales' => Sale::active()->count(),
            'cancelled_sales' => Sale::cancelled()->count(),
            'total_revenue' => (float) Sale::active()->sum('total_amount'),
            'average_sale_amount' => (float) Sale::active()->avg('total_amount'),
            'sales_today' => Sale::whereDate('sale_date', today())->count(),
            'revenue_today' => (float) Sale::whereDate('sale_date', today())->sum('total_amount'),
        ];
    }

    /**
     * Obtiene productos más vendidos.
     */
    public function getTopSellingProducts(int $limit = 10): Collection
    {
        return DB::table('sale_details')
            ->join('products', 'sale_details.product_id', '=', 'products.id')
            ->select('products.id', 'products.name', DB::raw('SUM(sale_details.quantity) as total_sold'))
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_sold', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Obtiene totales mensuales de ventas.
     */
    public function getMonthlyTotals(int $year = null): Collection
    {
        $year = $year ?? date('Y');
        
        return Sale::select(
            DB::raw('MONTH(sale_date) as month'),
            DB::raw('SUM(total_amount) as total_revenue'),
            DB::raw('COUNT(*) as total_sales')
        )
        ->whereYear('sale_date', $year)
        ->where('status', 'active')
        ->groupBy('month')
        ->orderBy('month')
        ->get();
    }
}