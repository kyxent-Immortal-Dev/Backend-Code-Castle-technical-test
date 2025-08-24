<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Http\Requests\SearchClientRequest;
use App\Repositories\ClientRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function __construct(
        private ClientRepository $clientRepository
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $clients = $this->clientRepository->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'data' => $clients
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClientRequest $request): JsonResponse
    {
        try {
            $client = $this->clientRepository->create($request->validated());
            
            return response()->json([
                'success' => true,
                'message' => 'Cliente creado exitosamente',
                'data' => $client
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $client = $this->clientRepository->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $client
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente no encontrado'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClientRequest $request, int $id): JsonResponse
    {
        try {
            $updated = $this->clientRepository->update($id, $request->validated());
            
            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cliente no encontrado'
                ], 404);
            }
            
            $client = $this->clientRepository->find($id);
            
            return response()->json([
                'success' => true,
                'message' => 'Cliente actualizado exitosamente',
                'data' => $client
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->clientRepository->delete($id);
            
            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el cliente porque tiene ventas asociadas'
                ], 400);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Cliente eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search clients by criteria.
     */
    public function search(SearchClientRequest $request): JsonResponse
    {
        try {
            $clients = $this->clientRepository->search($request->validated());
            
            return response()->json([
                'success' => true,
                'data' => $clients
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en la búsqueda: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get active clients.
     */
    public function active(): JsonResponse
    {
        try {
            $clients = $this->clientRepository->getActiveClients();
            
            return response()->json([
                'success' => true,
                'data' => $clients
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener clientes activos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle client status.
     */
    public function toggleStatus(int $id): JsonResponse
    {
        try {
            $updated = $this->clientRepository->toggleStatus($id);
            
            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cliente no encontrado'
                ], 404);
            }
            
            $client = $this->clientRepository->find($id);
            
            return response()->json([
                'success' => true,
                'message' => 'Estado del cliente actualizado exitosamente',
                'data' => $client
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el estado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get client statistics.
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = $this->clientRepository->getClientStats();
            
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
}