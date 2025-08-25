<?php

namespace App\Http\Controllers\Inventary;

use App\Repositories\Inventary\PurchaseRepository;
use App\Http\Requests\Inventary\Suppliers\StorePurchaseRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Purchase;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;

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
    public function show(Purchase $purchase): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $purchase->load(['supplier', 'user', 'details.product']),
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
     * Actualiza una compra existente.
     */
    public function update(StorePurchaseRequest $request, Purchase $purchase): JsonResponse
    {
        try {
            // Verificar que la compra esté pendiente
            if (!$purchase->isPending()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden actualizar compras pendientes'
                ], 400);
            }

            $data = $request->validated();
            $updated = $this->purchaseRepository->update($purchase->id, $data);

            if ($updated) {
                // Recargar la compra con las relaciones
                $purchase->refresh();
                $purchase->load(['supplier', 'user', 'details.product']);

                return response()->json([
                    'success' => true,
                    'data' => $purchase,
                    'message' => 'Compra actualizada exitosamente'
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la compra'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar compra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Elimina una compra.
     */
    public function destroy(Purchase $purchase): JsonResponse
    {
        try {
            if (!$purchase->isPending()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden eliminar compras pendientes'
                ], 400);
            }

            $deleted = $this->purchaseRepository->delete($purchase->id);

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Compra eliminada exitosamente'
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la compra'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar compra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marca una compra como completada.
     */
    public function complete(Purchase $purchase): JsonResponse
    {
        try {
            if (!$purchase->isPending()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden completar compras pendientes'
                ], 400);
            }

            $completed = $this->purchaseRepository->completePurchase($purchase->id);

            if ($completed) {
                $purchase->refresh();
                $purchase->load(['supplier', 'user', 'details.product']);

                return response()->json([
                    'success' => true,
                    'data' => $purchase,
                    'message' => 'Compra marcada como completada exitosamente'
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al completar la compra'
            ], 500);

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
    public function cancel(Purchase $purchase): JsonResponse
    {
        try {
            if (!$purchase->isPending()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden cancelar compras pendientes'
                ], 400);
            }

            $cancelled = $this->purchaseRepository->cancelPurchase($purchase->id);

            if ($cancelled) {
                $purchase->refresh();
                $purchase->load(['supplier', 'user', 'details.product']);

                return response()->json([
                    'success' => true,
                    'data' => $purchase,
                    'message' => 'Compra marcada como cancelada exitosamente'
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar la compra'
            ], 500);

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

    /**
     * Genera un reporte PDF de compras por proveedor.
     */
    public function generatePurchasesBySupplierReport(int $supplierId)
    {
        try {
            $supplier = Supplier::find($supplierId);
            
            if (!$supplier) {
                return response()->json([
                    'success' => false,
                    'message' => 'Proveedor no encontrado'
                ], 404);
            }

            $purchases = $this->purchaseRepository->getBySupplier($supplierId);
            
            // Asegurar que $purchases sea siempre una colección
            if (!$purchases) {
                $purchases = collect([]);
            }
            
            // Calcular estadísticas para el reporte
            $totalPurchases = $purchases->count();
            $pendingPurchases = $purchases->where('status', 'pending')->count();
            $completedPurchases = $purchases->where('status', 'completed')->count();
            $totalAmount = $purchases->sum('total_amount');

            $data = [
                'supplier' => $supplier,
                'purchases' => $purchases,
                'totalPurchases' => $totalPurchases,
                'pendingPurchases' => $pendingPurchases,
                'completedPurchases' => $completedPurchases,
                'totalAmount' => $totalAmount,
            ];

            $pdf = Pdf::loadView('pdf.purchases-by-supplier-report', $data);
            
            // Configurar el PDF
            $pdf->setPaper('a4', 'portrait');
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'DejaVu Sans'
            ]);

            $filename = 'reporte-compras-proveedor-' . $supplier->name . '-' . now()->format('Y-m-d-H-i-s') . '.pdf';
            
            return $pdf->download($filename);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar reporte: ' . $e->getMessage()
            ], 500);
        }
    }
}
