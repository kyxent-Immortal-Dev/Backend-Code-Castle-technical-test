<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class UserRepository 
{
    /**
     * Get all users
     */
    public function all(): Collection
    {
        return User::all();
    }

    /**
     * Find user by ID
     */
    public function find($id): ?User
    {
        return User::find($id);
    }

    /**
     * Create new user
     */
    public function create(array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        return User::create($data);
    }

    /**
     * Update user
     */
    public function update($id, array $data): bool
    {
        $user = $this->find($id);
        
        if (!$user) {
            return false;
        }

        // Hash password if provided
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $user->update($data);
    }

    /**
     * Delete user
     */
    public function delete($id): bool
    {
        $user = $this->find($id);
        
        if (!$user) {
            return false;
        }

        return $user->delete();
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Paginated search
     */
    public function paginate(int $perPage = 15)
    {
        return User::paginate($perPage);
    }

    /**
     * Search with filters
     */
    public function search(array $filters)
    {
        $query = User::query();

        if (isset($filters['name'])) {
            $query->where('name', 'ILIKE', '%' . $filters['name'] . '%');
        }

        if (isset($filters['email'])) {
            $query->where('email', 'ILIKE', '%' . $filters['email'] . '%');
        }

        if (isset($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->get();
    }

    /**
     * Get users by role
     */
    public function getByRole(string $role): Collection
    {
        return User::where('role', $role)->get();
    }

    /**
     * Get active users
     */
    public function getActive(): Collection
    {
        return User::where('is_active', true)->get();
    }

    /**
     * Get inactive users
     */
    public function getInactive(): Collection
    {
        return User::where('is_active', false)->get();
    }
}