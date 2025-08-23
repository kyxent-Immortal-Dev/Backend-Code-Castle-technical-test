<?php

namespace App\Http\Controllers;

use App\Repositories\PurchaseRepository;
use App\Http\Requests\StorePurchaseRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

/**
 * Controlador para el manejo de compras del sistema
 */
class PurchaseController extends Controller
{
    private PurchaseRepository $purchaseRepository;

    public function __construct(PurchaseRepository $purchaseRepository)
    {
        $this->purchaseRepository = $purchaseRepository;
    }

    /**
     * Obtiene la lista de compras con paginación.
     */
    public function index(): JsonResponse
    {
        try {
            $purchases = $this->purchaseRepository->paginate(
                request('per_page', 15)
            );

            return response()->json([
                'success' => true,
                'data' => $purchases,
                'message' => 'Compras obtenidas exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener compras: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Almacena una nueva compra en el sistema.
     */
    public function store(StorePurchaseRequest $request): JsonResponse
    {
        try {
            // Agregar el ID del usuario autenticado
            $data = $request->validated();
            $data['user_id'] = Auth::id();

            $purchase = $this->purchaseRepository->create($data);

            return response()->json([
                'success' => true,
                'data' => $purchase,
                'message' => 'Compra registrada exitosamente'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar compra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Muestra una compra específica.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $purchase = $this->purchaseRepository->find($id);

            if (!$purchase) {
                return response()->json([
                    'success' => false,
                    'message' => 'Compra no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $purchase,
                'message' => 'Compra obtenida exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener compra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Elimina una compra del sistema.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $purchase = $this->purchaseRepository->find($id);

            if (!$purchase) {
                return response()->json([
                    'success' => false,
                    'message' => 'Compra no encontrada'
                ], 404);
            }

            // Solo se pueden eliminar compras pendientes
            if (!$purchase->isPending()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden eliminar compras pendientes'
                ], 400);
            }

            $deleted = $this->purchaseRepository->delete($id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo eliminar la compra'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Compra eliminada exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar compra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marca una compra como completada y actualiza el stock.
     */
    public function complete(int $id): JsonResponse
    {
        try {
            $purchase = $this->purchaseRepository->find($id);

            if (!$purchase) {
                return response()->json([
                    'success' => false,
                    'message' => 'Compra no encontrada'
                ], 404);
            }

            // Solo se pueden completar compras pendientes
            if (!$purchase->isPending()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden completar compras pendientes'
                ], 400);
            }

            $completed = $this->purchaseRepository->completePurchase($id);

            if (!$completed) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo completar la compra'
                ], 500);
            }

            $updatedPurchase = $this->purchaseRepository->find($id);

            return response()->json([
                'success' => true,
                'data' => $updatedPurchase,
                'message' => 'Compra completada exitosamente. El stock de los productos ha sido actualizado.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al completar compra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marca una compra como cancelada.
     */
    public function cancel(int $id): JsonResponse
    {
        try {
            $purchase = $this->purchaseRepository->find($id);

            if (!$purchase) {
                return response()->json([
                    'success' => false,
                    'message' => 'Compra no encontrada'
                ], 404);
            }

            // Solo se pueden cancelar compras pendientes
            if (!$purchase->isPending()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden cancelar compras pendientes'
                ], 400);
            }

            $cancelled = $this->purchaseRepository->cancelPurchase($id);

            if (!$cancelled) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo cancelar la compra'
                ], 500);
            }

            $updatedPurchase = $this->purchaseRepository->find($id);

            return response()->json([
                'success' => true,
                'data' => $updatedPurchase,
                'message' => 'Compra cancelada exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar compra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene compras por estado.
     */
    public function byStatus(string $status): JsonResponse
    {
        try {
            $purchases = $this->purchaseRepository->getByStatus($status);

            return response()->json([
                'success' => true,
                'data' => $purchases,
                'message' => "Compras con estado '{$status}' obtenidas exitosamente"
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener compras por estado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene compras en un rango de fechas.
     */
    public function byDateRange(): JsonResponse
    {
        try {
            $startDate = request('start_date');
            $endDate = request('end_date');

            if (!$startDate || !$endDate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Las fechas de inicio y fin son obligatorias'
                ], 400);
            }

            $purchases = $this->purchaseRepository->getByDateRange($startDate, $endDate);

            return response()->json([
                'success' => true,
                'data' => $purchases,
                'message' => 'Compras por rango de fechas obtenidas exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener compras por rango de fechas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene compras por proveedor.
     */
    public function bySupplier(int $supplierId): JsonResponse
    {
        try {
            $purchases = $this->purchaseRepository->getBySupplier($supplierId);

            return response()->json([
                'success' => true,
                'data' => $purchases,
                'message' => 'Compras por proveedor obtenidas exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener compras por proveedor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene estadísticas de compras.
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = $this->purchaseRepository->getPurchaseStats();

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Estadísticas de compras obtenidas exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas de compras: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene el total de compras por mes en el último año.
     */
    public function monthlyTotals(): JsonResponse
    {
        try {
            $monthlyTotals = $this->purchaseRepository->getMonthlyTotals();

            return response()->json([
                'success' => true,
                'data' => $monthlyTotals,
                'message' => 'Totales mensuales de compras obtenidos exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener totales mensuales: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene los productos más comprados.
     */
    public function topProducts(): JsonResponse
    {
        try {
            $limit = request('limit', 10);
            $topProducts = $this->purchaseRepository->getTopProducts($limit);

            return response()->json([
                'success' => true,
                'data' => $topProducts,
                'message' => 'Productos más comprados obtenidos exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener productos más comprados: ' . $e->getMessage()
            ], 500);
        }
    }
}
