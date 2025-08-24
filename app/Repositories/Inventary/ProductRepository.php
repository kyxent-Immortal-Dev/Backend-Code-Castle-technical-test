<?php

namespace App\Repositories\Inventary;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Repositorio para el manejo de productos del inventario
 */
class ProductRepository
{
    /**
     * Obtiene todos los productos.
     */
    public function all(): Collection
    {
        return Product::with('purchaseDetails')->get();
    }

    /**
     * Obtiene todos los productos con paginación.
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Product::with('purchaseDetails')
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Busca productos por criterios específicos.
     */
    public function search(array $criteria): Collection
    {
        $query = Product::query();

        if (isset($criteria['name'])) {
            $query->where('name', 'like', '%' . $criteria['name'] . '%');
        }

        if (isset($criteria['description'])) {
            $query->where('description', 'like', '%' . $criteria['description'] . '%');
        }

        if (isset($criteria['min_price'])) {
            $query->where('unit_price', '>=', $criteria['min_price']);
        }

        if (isset($criteria['max_price'])) {
            $query->where('unit_price', '<=', $criteria['max_price']);
        }

        if (isset($criteria['in_stock'])) {
            if ($criteria['in_stock']) {
                $query->inStock();
            } else {
                $query->where('stock', '<=', 0);
            }
        }

        if (isset($criteria['low_stock'])) {
            $query->lowStock();
        }

        return $query->with('purchaseDetails')->get();
    }

    /**
     * Obtiene un producto por su ID.
     */
    public function find(int $id): ?Product
    {
        return Product::with('purchaseDetails')->find($id);
    }

    /**
     * Obtiene un producto por su ID o lanza una excepción.
     */
    public function findOrFail(int $id): Product
    {
        return Product::with('purchaseDetails')->findOrFail($id);
    }

    /**
     * Crea un nuevo producto.
     */
    public function create(array $data): Product
    {
        return Product::create($data);
    }

    /**
     * Actualiza un producto existente.
     */
    public function update(int $id, array $data): bool
    {
        $product = $this->find($id);
        
        if (!$product) {
            return false;
        }

        return $product->update($data);
    }

    /**
     * Elimina un producto.
     */
    public function delete(int $id): bool
    {
        $product = $this->find($id);
        
        if (!$product) {
            return false;
        }

        // Verificar si el producto tiene compras asociadas
        if ($product->purchaseDetails()->exists()) {
            return false; // No se puede eliminar si tiene historial de compras
        }

        return $product->delete();
    }

    /**
     * Obtiene productos con stock bajo.
     */
    public function getLowStockProducts(int $threshold = 10): Collection
    {
        return Product::lowStock()->get();
    }

    /**
     * Obtiene productos sin stock.
     */
    public function getOutOfStockProducts(): Collection
    {
        return Product::where('stock', '<=', 0)->get();
    }

    /**
     * Obtiene productos activos.
     */
    public function getActiveProducts(): Collection
    {
        return Product::active()->get();
    }

    /**
     * Aumenta el stock de un producto.
     */
    public function increaseStock(int $productId, int $quantity): bool
    {
        $product = $this->find($productId);
        
        if (!$product) {
            return false;
        }

        return $product->increaseStock($quantity);
    }

    /**
     * Disminuye el stock de un producto.
     */
    public function decreaseStock(int $productId, int $quantity): bool
    {
        $product = $this->find($productId);
        
        if (!$product) {
            return false;
        }

        return $product->decreaseStock($quantity);
    }

    /**
     * Obtiene el valor total del inventario.
     */
    public function getTotalInventoryValue(): float
    {
        return (float) Product::sum(DB::raw('stock * unit_price'));
    }

    /**
     * Obtiene estadísticas del inventario.
     */
    public function getInventoryStats(): array
    {
        return [
            'total_products' => Product::count(),
            'active_products' => Product::active()->count(),
            'low_stock_products' => Product::lowStock()->count(),
            'out_of_stock_products' => Product::where('stock', '<=', 0)->count(),
            'total_inventory_value' => $this->getTotalInventoryValue(),
            'average_price' => (float) Product::avg('unit_price'),
        ];
    }

    /**
     * Cambia el estado activo/inactivo de un producto.
     */
    public function toggleStatus(int $id): bool
    {
        $product = $this->find($id);
        
        if (!$product) {
            return false;
        }

        $product->is_active = !$product->is_active;
        return $product->save();
    }

    /**
     * Obtiene productos por rango de precios.
     */
    public function getProductsByPriceRange(float $minPrice, float $maxPrice): Collection
    {
        return Product::whereBetween('unit_price', [$minPrice, $maxPrice])
            ->orderBy('unit_price')
            ->get();
    }

    /**
     * Obtiene productos ordenados por stock (ascendente).
     */
    public function getProductsByStockAsc(): Collection
    {
        return Product::orderBy('stock', 'asc')->get();
    }

    /**
     * Obtiene productos ordenados por stock (descendente).
     */
    public function getProductsByStockDesc(): Collection
    {
        return Product::orderBy('stock', 'desc')->get();
    }
} 