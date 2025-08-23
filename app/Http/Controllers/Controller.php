<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="System Inventory API",
 *     description="Complete API for user management with authentication and role-based access control. This API provides endpoints for user registration, authentication, and comprehensive user management with admin and vendedor roles.",
 *     @OA\Contact(
 *         email="admin@system.com",
 *         name="System Administrator",
 *         url="https://github.com/your-repo"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Local Development Server"
 * )
 * 
 * @OA\Server(
 *     url="https://your-production-domain.com/api",
 *     description="Production Server"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Enter the token with the `Bearer ` prefix, e.g. `Bearer abcde12345`"
 * )
 * 
 * @OA\Tag(
 *     name="Authentication",
 *     description="User authentication endpoints including registration, login, logout, profile management, and token refresh"
 * )
 * 
 * @OA\Tag(
 *     name="Users",
 *     description="User management endpoints for administrators to create, read, update, delete, and manage user status"
 * )
 * 
 * @OA\ExternalDocumentation(
 *     description="Find more info about this API",
 *     url="https://github.com/your-repo"
 * )
 * 
 * @OA\Schema(
 *     schema="User",
 *     required={"id", "name", "email", "role", "is_active"},
 *     @OA\Property(property="id", type="integer", example=1, description="Unique identifier for the user"),
 *     @OA\Property(property="name", type="string", example="John Doe", description="Full name of the user"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com", description="Unique email address"),
 *     @OA\Property(property="role", type="string", enum={"admin", "vendedor"}, example="admin", description="User role in the system"),
 *     @OA\Property(property="is_active", type="boolean", example=true, description="Whether the user account is active"),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true, description="Email verification timestamp"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Account creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update timestamp")
 * )
 * 
 * @OA\Schema(
 *     schema="LoginRequest",
 *     required={"email", "password"},
 *     @OA\Property(property="email", type="string", format="email", example="admin@system.com", description="User's email address"),
 *     @OA\Property(property="password", type="string", example="password123", description="User's password (minimum 8 characters)")
 * )
 * 
 * @OA\Schema(
 *     schema="RegisterRequest",
 *     required={"name", "email", "password", "password_confirmation"},
 *     @OA\Property(property="name", type="string", example="John Doe", description="Full name of the user"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com", description="Unique email address"),
 *     @OA\Property(property="password", type="string", minLength=8, example="password123", description="Password (minimum 8 characters)"),
 *     @OA\Property(property="password_confirmation", type="string", example="password123", description="Password confirmation (must match password)"),
 *     @OA\Property(property="role", type="string", enum={"admin", "vendedor"}, example="vendedor", description="User role (defaults to vendedor if not specified)")
 * )
 * 
 * @OA\Schema(
 *     schema="CreateUserRequest",
 *     required={"name", "email", "password", "role"},
 *     @OA\Property(property="name", type="string", example="New User", description="Full name of the user"),
 *     @OA\Property(property="email", type="string", format="email", example="newuser@example.com", description="Unique email address"),
 *     @OA\Property(property="password", type="string", minLength=8, example="password123", description="Password (minimum 8 characters)"),
 *     @OA\Property(property="role", type="string", enum={"admin", "vendedor"}, example="vendedor", description="User role in the system")
 * )
 * 
 * @OA\Schema(
 *     schema="UpdateUserRequest",
 *     @OA\Property(property="name", type="string", example="Updated Name", description="Full name of the user"),
 *     @OA\Property(property="email", type="string", format="email", example="updated@example.com", description="Unique email address"),
 *     @OA\Property(property="password", type="string", minLength=8, example="newpassword123", description="New password (minimum 8 characters)"),
 *     @OA\Property(property="role", type="string", enum={"admin", "vendedor"}, example="admin", description="User role in the system"),
 *     @OA\Property(property="is_active", type="boolean", example=true, description="Whether the user account is active")
 * )
 * 
 * @OA\Schema(
 *     schema="SuccessResponse",
 *     @OA\Property(property="success", type="boolean", example=true, description="Indicates if the operation was successful"),
 *     @OA\Property(property="data", type="object", description="Response data (varies by endpoint)"),
 *     @OA\Property(property="message", type="string", example="Operation completed successfully", description="Human-readable success message")
 * )
 * 
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     @OA\Property(property="success", type="boolean", example=false, description="Indicates if the operation was successful"),
 *     @OA\Property(property="message", type="string", example="Error message", description="Human-readable error message"),
 *     @OA\Property(property="errors", type="object", nullable=true, description="Detailed error information (if available)")
 * )
 * 
 * @OA\Schema(
 *     schema="ValidationErrorResponse",
 *     @OA\Property(property="success", type="boolean", example=false, description="Indicates if the operation was successful"),
 *     @OA\Property(property="message", type="string", example="Validation errors", description="Human-readable error message"),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         description="Field-specific validation errors",
 *         @OA\Property(property="email", type="array", @OA\Items(type="string"), example={"The email field is required."}),
 *         @OA\Property(property="password", type="array", @OA\Items(type="string"), example={"The password must be at least 8 characters."})
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="PaginatedResponse",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(
 *         property="data",
 *         type="object",
 *         @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/User")),
 *         @OA\Property(property="current_page", type="integer", example=1),
 *         @OA\Property(property="last_page", type="integer", example=5),
 *         @OA\Property(property="per_page", type="integer", example=15),
 *         @OA\Property(property="total", type="integer", example=75)
 *     ),
 *     @OA\Property(property="message", type="string", example="Users retrieved successfully")
 * )
 */
abstract class Controller
{
    //
}