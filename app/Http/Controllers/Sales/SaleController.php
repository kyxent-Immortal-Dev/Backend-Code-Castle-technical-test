<?php

namespace App\Http\Controllers\Sales;

use App\Http\Requests\Sales\Sales\StoreSaleRequest;
use App\Repositories\Sales\SaleRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SaleController extends Controller
{
    public function __construct(
        private SaleRepository $saleRepository
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $sales = $this->saleRepository->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'data' => $sales
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSaleRequest $request): JsonResponse
    {
        try {
            // Agregar el usuario autenticado
            $data = $request->validated();
            $data['user_id'] = auth()->id();
            
            $sale = $this->saleRepository->create($data);
            
            return response()->json([
                'success' => true,
                'message' => 'Venta registrada exitosamente',
                'data' => $sale
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la venta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $sale = $this->saleRepository->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $sale
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Venta no encontrada'
            ], 404);
        }
    }

    /**
     * Cancel a sale.
     */
    public function cancel(int $id): JsonResponse
    {
        try {
            $cancelled = $this->saleRepository->cancel($id);
            
            if (!$cancelled) {
                return response()->json([
                    'success' => false,
                    'message' => 'La venta ya está cancelada o no se pudo cancelar'
                ], 400);
            }
            
            $sale = $this->saleRepository->find($id);
            
            return response()->json([
                'success' => true,
                'message' => 'Venta cancelada exitosamente',
                'data' => $sale
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar la venta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sales by client.
     */
    public function byClient(int $clientId): JsonResponse
    {
        try {
            $sales = $this->saleRepository->getSalesByClient($clientId);
            
            return response()->json([
                'success' => true,
                'data' => $sales
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener ventas del cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sales by user.
     */
    public function byUser(int $userId): JsonResponse
    {
        try {
            $sales = $this->saleRepository->getSalesByUser($userId);
            
            return response()->json([
                'success' => true,
                'data' => $sales
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener ventas del usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sales by date range.
     */
    public function byDateRange(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date'
            ]);
            
            $sales = $this->saleRepository->getSalesByDateRange(
                $request->start_date,
                $request->end_date
            );
            
            return response()->json([
                'success' => true,
                'data' => $sales
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener ventas por rango de fechas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sales by status.
     */
    public function byStatus(string $status): JsonResponse
    {
        try {
            $sales = $this->saleRepository->getSalesByStatus($status);
            
            return response()->json([
                'success' => true,
                'data' => $sales
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener ventas por estado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sales statistics.
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = $this->saleRepository->getSalesStats();
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get top selling products.
     */
    public function topProducts(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 10);
            $products = $this->saleRepository->getTopSellingProducts($limit);
            
            return response()->json([
                'success' => true,
                'data' => $products
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener productos más vendidos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get monthly totals.
     */
    public function monthlyTotals(Request $request): JsonResponse
    {
        try {
            $year = $request->get('year', date('Y'));
            $totals = $this->saleRepository->getMonthlyTotals($year);
            
            return response()->json([
                'success' => true,
                'data' => $totals
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener totales mensuales: ' . $e->getMessage()
            ], 500);
        }
    }
}