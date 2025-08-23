<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

# System Inventory API

A Laravel-based REST API for user management with authentication and role-based access control.

## Features

- **Authentication System**: Laravel Sanctum integration for token-based authentication
- **Role-Based Access Control**: Admin and Vendedor roles with different permissions
- **User Management**: Full CRUD operations for user management (admin only)
- **User Status Management**: Ability to activate/deactivate users
- **RESTful API**: JSON-based responses for easy frontend integration
- **Interactive API Documentation**: Swagger/OpenAPI 3.0 documentation with testing interface

## Requirements

- PHP 8.2+
- Laravel 12.x
- MySQL/PostgreSQL/SQLite
- Composer

## Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd SystemInventary
```

2. Install dependencies:
```bash
composer install
```

3. Copy environment file:
```bash
cp .env.example .env
```

4. Configure your database in `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=system_inventory
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

5. Generate application key:
```bash
php artisan key:generate
```

6. Run migrations:
```bash
php artisan migrate
```

7. Seed the database with default users:
```bash
php artisan db:seed --class=AdminUserSeeder
```

8. Start the development server:
```bash
php artisan serve
```

## Default Users

After running the seeder, you'll have these default accounts:

- **Admin**: `admin@system.com` / `admin123`
- **Vendedor**: `vendedor@system.com` / `vendedor123`

## API Documentation

### Swagger UI (Interactive)
Access the interactive API documentation at: `http://localhost:8000/api/documentation`

Features:
- Interactive endpoint testing
- Request/response schemas
- Authentication testing
- Parameter examples
- Response examples

### OpenAPI Specification
Raw OpenAPI spec: `http://localhost:8000/docs`

Use this for:
- Postman/Insomnia import
- Code generation
- API client generation
- CI/CD integration

### Documentation Files
- **API_DOCUMENTATION.md**: Complete API reference with examples
- **SWAGGER_USAGE.md**: Detailed guide on using Swagger documentation
- **README.md**: This file with setup instructions

## Quick Start

1. **Login to get a token:**
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@system.com","password":"admin123"}'
```

2. **Use the token to access protected endpoints:**
```bash
curl -X GET http://localhost:8000/api/users \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

3. **Explore the API with Swagger UI:**
   - Visit `http://localhost:8000/api/documentation`
   - Click "Authorize" and enter your Bearer token
   - Test any endpoint directly from the browser

## Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php      # Authentication endpoints
│   │   ├── UserController.php      # User management endpoints
│   │   └── SwaggerController.php   # API documentation
│   ├── Middleware/
│   │   ├── CheckRole.php          # Role-based access control
│   │   └── HandleAuthenticationErrors.php # Auth error handling
│   └── Repositories/
│       └── UserRepository.php      # User data operations
├── Models/
│   └── User.php                   # User model with roles
routes/
└── api.php                        # API route definitions
config/
└── l5-swagger.php                 # Swagger configuration
```

## Security Features

- Password hashing with Laravel Hash
- Token-based authentication with Sanctum
- Role-based middleware protection
- Input validation and sanitization
- CSRF protection (where applicable)
- Proper HTTP status codes for authentication errors

## Development

### Adding New Endpoints

1. Create controller methods with Swagger annotations
2. Add routes in `routes/api.php`
3. Apply appropriate middleware
4. Update documentation
5. Regenerate Swagger docs: `php artisan l5-swagger:generate`

### Swagger Documentation

The API uses OpenAPI 3.0 annotations for automatic documentation generation:

```php
/**
 * @OA\Post(
 *     path="/endpoint",
 *     summary="Endpoint description",
 *     tags={"TagName"},
 *     @OA\RequestBody(...),
 *     @OA\Response(...)
 * )
 */
public function method(Request $request): JsonResponse
```

### Testing

The API includes comprehensive error handling and validation. Test all endpoints with various scenarios:

- Valid/invalid authentication
- Role-based access control
- Input validation
- Error responses
- Use Swagger UI for interactive testing

## API Response Format

All API responses follow a consistent format:

```json
{
    "success": true,
    "data": {...},
    "message": "Operation completed successfully"
}
```

Error responses:
```json
{
    "success": false,
    "message": "Error description",
    "errors": {...} // Optional validation errors
}
```

## HTTP Status Codes

- **200**: Success
- **201**: Created
- **400**: Bad Request
- **401**: Unauthenticated
- **403**: Forbidden (insufficient permissions)
- **404**: Not Found
- **422**: Validation Error
- **500**: Server Error

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
