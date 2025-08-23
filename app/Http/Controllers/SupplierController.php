<?php

namespace App\Http\Controllers;

use App\Repositories\SupplierRepository;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

/**
 * Controlador para el manejo de proveedores del sistema
 */
class SupplierController extends Controller
{
    private SupplierRepository $supplierRepository;

    public function __construct(SupplierRepository $supplierRepository)
    {
        $this->supplierRepository = $supplierRepository;
    }

    /**
     * Obtiene la lista de proveedores con paginación.
     */
    public function index(): JsonResponse
    {
        try {
            $suppliers = $this->supplierRepository->paginate(
                request('per_page', 15)
            );

            return response()->json([
                'success' => true,
                'data' => $suppliers,
                'message' => 'Proveedores obtenidos exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener proveedores: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Almacena un nuevo proveedor en el sistema.
     */
    public function store(StoreSupplierRequest $request): JsonResponse
    {
        try {
            $supplier = $this->supplierRepository->create($request->validated());

            return response()->json([
                'success' => true,
                'data' => $supplier,
                'message' => 'Proveedor creado exitosamente'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear proveedor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Muestra un proveedor específico.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $supplier = $this->supplierRepository->find($id);

            if (!$supplier) {
                return response()->json([
                    'success' => false,
                    'message' => 'Proveedor no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $supplier,
                'message' => 'Proveedor obtenido exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener proveedor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualiza un proveedor existente.
     */
    public function update(UpdateSupplierRequest $request, int $id): JsonResponse
    {
        try {
            $supplier = $this->supplierRepository->find($id);

            if (!$supplier) {
                return response()->json([
                    'success' => false,
                    'message' => 'Proveedor no encontrado'
                ], 404);
            }

            $updated = $this->supplierRepository->update($id, $request->validated());

            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo actualizar el proveedor'
                ], 500);
            }

            $updatedSupplier = $this->supplierRepository->find($id);

            return response()->json([
                'success' => true,
                'data' => $updatedSupplier,
                'message' => 'Proveedor actualizado exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar proveedor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Elimina un proveedor del sistema.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $supplier = $this->supplierRepository->find($id);

            if (!$supplier) {
                return response()->json([
                    'success' => false,
                    'message' => 'Proveedor no encontrado'
                ], 404);
            }

            $deleted = $this->supplierRepository->delete($id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo eliminar el proveedor. Verifique que no tenga compras asociadas.'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Proveedor eliminado exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar proveedor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambia el estado activo/inactivo de un proveedor.
     */
    public function toggleStatus(int $id): JsonResponse
    {
        try {
            $supplier = $this->supplierRepository->find($id);

            if (!$supplier) {
                return response()->json([
                    'success' => false,
                    'message' => 'Proveedor no encontrado'
                ], 404);
            }

            $updated = $this->supplierRepository->toggleStatus($id);

            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo cambiar el estado del proveedor'
                ], 500);
            }

            $updatedSupplier = $this->supplierRepository->find($id);

            return response()->json([
                'success' => true,
                'data' => $updatedSupplier,
                'message' => 'Estado del proveedor cambiado exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar estado del proveedor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene proveedores activos.
     */
    public function active(): JsonResponse
    {
        try {
            $suppliers = $this->supplierRepository->getActiveSuppliers();

            return response()->json([
                'success' => true,
                'data' => $suppliers,
                'message' => 'Proveedores activos obtenidos exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener proveedores activos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene el proveedor con más compras.
     */
    public function topSupplier(): JsonResponse
    {
        try {
            $supplier = $this->supplierRepository->getTopSupplier();

            return response()->json([
                'success' => true,
                'data' => $supplier,
                'message' => 'Proveedor principal obtenido exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener proveedor principal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene proveedores ordenados por monto total de compras.
     */
    public function byTotalAmount(): JsonResponse
    {
        try {
            $suppliers = $this->supplierRepository->getSuppliersByTotalAmount();

            return response()->json([
                'success' => true,
                'data' => $suppliers,
                'message' => 'Proveedores ordenados por monto total obtenidos exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener proveedores por monto total: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene estadísticas de proveedores.
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = $this->supplierRepository->getSupplierStats();

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Estadísticas de proveedores obtenidas exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas de proveedores: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Busca proveedores por criterios específicos.
     */
    public function search(): JsonResponse
    {
        try {
            $criteria = request()->only(['name', 'email', 'phone', 'has_purchases']);
            
            $suppliers = $this->supplierRepository->search($criteria);

            return response()->json([
                'success' => true,
                'data' => $suppliers,
                'message' => 'Búsqueda de proveedores completada exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en la búsqueda de proveedores: ' . $e->getMessage()
            ], 500);
        }
    }
}
