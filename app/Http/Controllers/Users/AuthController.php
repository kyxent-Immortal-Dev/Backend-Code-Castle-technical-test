<?php

namespace App\Http\Controllers\Users;

use App\Models\User;
use App\Http\Requests\Users\RegisterUserRequest;
use App\Http\Requests\Users\LoginUserRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    public function register(RegisterUserRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            // Set default role if not provided
            if (!isset($validated['role'])) {
                $validated['role'] = 'vendedor';
            }

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
                'is_active' => true,
            ]);

            // Log in the user after registration
            Auth::login($user);

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $user,
                ],
                'message' => 'User registered successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error registering user: ' . $e->getMessage()
            ], 500);
        }
    }

    public function login(LoginUserRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            if (!Auth::attempt($validated)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            $user = User::where('email', $validated['email'])->first();

            if (!$user->isActive()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account is deactivated'
                ], 401);
            }

            // Regenerate session for security
            $request->session()->regenerate();

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $user,
                ],
                'message' => 'Login successful'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error during login: ' . $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            // Logout and invalidate session
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json([
                'success' => true,
                'message' => 'Logout successful'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error during logout: ' . $e->getMessage()
            ], 500);
        }
    }

    public function profile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            return response()->json([
                'success' => true,
                'data' => $user,
                'message' => 'Profile retrieved successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving profile: ' . $e->getMessage()
            ], 500);
        }
    }

    public function refresh(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Regenerate session for security
            $request->session()->regenerate();

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $user,
                ],
                'message' => 'Session refreshed successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error refreshing session: ' . $e->getMessage()
            ], 500);
        }
    }
}
