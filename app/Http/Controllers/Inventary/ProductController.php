<?php

namespace App\Http\Controllers\Inventary;

use App\Repositories\Inventary\ProductRepository;
use App\Http\Requests\Inventary\Products\StoreProductRequest;
use App\Http\Requests\Inventary\Products\UpdateProductRequest;
use App\Http\Requests\Inventary\Products\SearchProductRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

/**
 * Controlador para el manejo de productos del inventario
 */
class ProductController extends Controller
{
    private ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Obtiene la lista de productos con opciones de búsqueda y paginación.
     */
    public function index(SearchProductRequest $request): JsonResponse
    {
        try {
            $criteria = $request->validated();
            
            // Si hay criterios de búsqueda específicos
            if (!empty(array_filter($criteria, fn($value) => $value !== null && $value !== ''))) {
                $products = $this->productRepository->search($criteria);
            } else {
                // Paginación si se solicita
                if ($request->has('page')) {
                    $products = $this->productRepository->paginate(
                        $request->get('per_page', 15)
                    );
                } else {
                    $products = $this->productRepository->all();
                }
            }

            return response()->json([
                'success' => true,
                'data' => $products,
                'message' => 'Productos obtenidos exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener productos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Almacena un nuevo producto en el inventario.
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        try {
            $product = $this->productRepository->create($request->validated());

            return response()->json([
                'success' => true,
                'data' => $product,
                'message' => 'Producto creado exitosamente'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear producto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Muestra un producto específico.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $product = $this->productRepository->find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $product,
                'message' => 'Producto obtenido exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener producto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualiza un producto existente.
     */
    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        try {
            $product = $this->productRepository->find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado'
                ], 404);
            }

            $updated = $this->productRepository->update($id, $request->validated());

            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo actualizar el producto'
                ], 500);
            }

            $updatedProduct = $this->productRepository->find($id);

            return response()->json([
                'success' => true,
                'data' => $updatedProduct,
                'message' => 'Producto actualizado exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar producto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Elimina un producto del inventario.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $product = $this->productRepository->find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado'
                ], 404);
            }

            $deleted = $this->productRepository->delete($id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo eliminar el producto. Verifique que no tenga compras asociadas.'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Producto eliminado exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar producto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambia el estado activo/inactivo de un producto.
     */
    public function toggleStatus(int $id): JsonResponse
    {
        try {
            $product = $this->productRepository->find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado'
                ], 404);
            }

            $updated = $this->productRepository->toggleStatus($id);

            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo cambiar el estado del producto'
                ], 500);
            }

            $updatedProduct = $this->productRepository->find($id);

            return response()->json([
                'success' => true,
                'data' => $updatedProduct,
                'message' => 'Estado del producto cambiado exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar estado del producto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene productos con stock bajo.
     */
    public function lowStock(): JsonResponse
    {
        try {
            $products = $this->productRepository->getLowStockProducts();

            return response()->json([
                'success' => true,
                'data' => $products,
                'message' => 'Productos con stock bajo obtenidos exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener productos con stock bajo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene productos sin stock.
     */
    public function outOfStock(): JsonResponse
    {
        try {
            $products = $this->productRepository->getOutOfStockProducts();

            return response()->json([
                'success' => true,
                'data' => $products,
                'message' => 'Productos sin stock obtenidos exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener productos sin stock: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene estadísticas del inventario.
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = $this->productRepository->getInventoryStats();

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Estadísticas del inventario obtenidas exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene productos por rango de precios.
     */
    public function byPriceRange(): JsonResponse
    {
        try {
            $minPrice = request('min_price', 0);
            $maxPrice = request('max_price', 999999.99);

            $products = $this->productRepository->getProductsByPriceRange($minPrice, $maxPrice);

            return response()->json([
                'success' => true,
                'data' => $products,
                'message' => 'Productos por rango de precios obtenidos exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener productos por rango de precios: ' . $e->getMessage()
            ], 500);
        }
    }
}
