<?php

namespace App\Repositories\Sales;

use App\Models\Client;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Repositorio para el manejo de clientes
 */
class ClientRepository
{
    /**
     * Obtiene todos los clientes.
     */
    public function all(): Collection
    {
        return Client::with('sales')->get();
    }

    /**
     * Obtiene todos los clientes con paginación.
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Client::with('sales')
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Busca clientes por criterios específicos.
     */
    public function search(array $criteria): Collection
    {
        $query = Client::query();

        if (isset($criteria['name'])) {
            $query->where('name', 'like', '%' . $criteria['name'] . '%');
        }

        if (isset($criteria['email'])) {
            $query->where('email', 'like', '%' . $criteria['email'] . '%');
        }

        if (isset($criteria['phone'])) {
            $query->where('phone', 'like', '%' . $criteria['phone'] . '%');
        }

        if (isset($criteria['is_active'])) {
            $query->where('is_active', $criteria['is_active']);
        }

        return $query->with('sales')->get();
    }

    /**
     * Obtiene un cliente por su ID.
     */
    public function find(int $id): ?Client
    {
        return Client::with('sales')->find($id);
    }

    /**
     * Obtiene un cliente por su ID o lanza una excepción.
     */
    public function findOrFail(int $id): Client
    {
        return Client::with('sales')->findOrFail($id);
    }

    /**
     * Crea un nuevo cliente.
     */
    public function create(array $data): Client
    {
        return Client::create($data);
    }

    /**
     * Actualiza un cliente existente.
     */
    public function update(int $id, array $data): bool
    {
        $client = $this->find($id);
        
        if (!$client) {
            return false;
        }

        return $client->update($data);
    }

    /**
     * Elimina un cliente.
     */
    public function delete(int $id): bool
    {
        $client = $this->find($id);
        
        if (!$client) {
            return false;
        }

        // Verificar si el cliente tiene ventas asociadas
        if ($client->sales()->exists()) {
            return false; // No se puede eliminar si tiene historial de ventas
        }

        return $client->delete();
    }

    /**
     * Obtiene clientes activos.
     */
    public function getActiveClients(): Collection
    {
        return Client::active()->get();
    }

    /**
     * Cambia el estado activo/inactivo de un cliente.
     */
    public function toggleStatus(int $id): bool
    {
        $client = $this->find($id);
        
        if (!$client) {
            return false;
        }

        $client->is_active = !$client->is_active;
        return $client->save();
    }

    /**
     * Obtiene estadísticas de clientes.
     */
    public function getClientStats(): array
    {
        return [
            'total_clients' => Client::count(),
            'active_clients' => Client::active()->count(),
            'clients_with_sales' => Client::has('sales')->count(),
            'top_clients' => Client::withCount('sales')
                ->orderBy('sales_count', 'desc')
                ->limit(5)
                ->get()
        ];
    }
}