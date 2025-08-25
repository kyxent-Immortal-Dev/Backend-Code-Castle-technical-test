<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>



# System Inventory API

A comprehensive Laravel-based REST API for inventory management with authentication, sales, and reporting capabilities, built with **Laravel + PostgreSQL**.

## Features

- **Authentication System**: Laravel Sanctum integration for token-based authentication
- **Role-Based Access Control**: Admin and Vendedor roles with different permissions
- **User Management**: Full CRUD operations for user management (admin only)
- **User Status Management**: Ability to activate/deactivate users
- **Inventory Management**: Complete product and supplier management
- **Purchase Management**: Purchase orders and supplier relationships
- **Sales Management**: Client management and sales processing
- **Reporting System**: PDF reports for sales, purchases, and stock
- **RESTful API**: JSON-based responses for easy frontend integration
- **Interactive API Documentation**: Swagger/OpenAPI 3.1 documentation with testing interface

## Autor
- Ezequiel Campos - full stack developer
- https://github.com/kyxent-Immortal-Dev

## Requirements

- PHP 8.2+
- Laravel 12.x
- PostgreSQL
- Composer

## Installation

1. Clone the repository:
```bash
git clone https://github.com/kyxent-Immortal-Dev/Backend-Code-Castle-technical-test.git
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

4. Configure your PostgreSQL database in `.env`:
```env
DB_CONNECTION=pgsql
DB_HOST=your-host
DB_PORT=your-port
DB_DATABASE=your-database
DB_USERNAME=postgres
DB_PASSWORD=your-password
FRONTEND_URL=tu-frontend:tu-puerto
SANCTUM_STATEFUL_DOMAINS=tuhost:tupuerto
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
php artisan db:seed
```

8. Start the development server:
```bash
php artisan serve
```

## Default Users

After running the seeder, you'll have these default accounts:

- **Admin**: `h.ezequiel.z.campos@codecastle.com` / `admin123`
- **Vendedor**: `vendedor@codecastle.com` / `vendedor123`

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

## Quick Start

1. **Login to get a token:**
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"h.ezequiel.z.campos@codecastle.com","password":"admin123"}'
```


2. **Explore the API with Swagger UI:**
   - Visit `http://localhost:8000/api/documentation`
   - Click "login with credentials" 
   - **Admin**: `h.ezequiel.z.campos@codecastle.com` / `admin123`
   - **Vendedor**: `vendedor@codecastle.com` / `vendedor123`
   - Test any endpoint directly from the browser



## Project Structure

```
app/
├── Console/
│   └── Commands/
│       ├── DebugSales.php
│       ├── GenerateApiDocs.php
│       └── UpdateSwaggerDocs.php
├── Http/
│   ├── Controllers/
│   │   ├── Controller.php
│   │   ├── Inventary/
│   │   │   ├── ProductController.php      # Product management
│   │   │   ├── PurchaseController.php     # Purchase orders
│   │   │   └── SupplierController.php     # Supplier management
│   │   ├── Sales/
│   │   │   ├── ClientController.php       # Client management
│   │   │   └── SaleController.php         # Sales processing
│   │   └── Users/
│   │       ├── AuthController.php         # Authentication endpoints
│   │       └── UserController.php         # User management
│   ├── Middleware/
│   │   ├── AdminMiddleware.php            # Admin role protection
│   │   ├── AuthenticatedMiddleware.php    # Authentication check
│   │   ├── CheckRole.php                  # Role-based access control
│   │   └── HandleAuthenticationErrors.php # Auth error handling
│   ├── Requests/
│   │   ├── Inventary/
│   │   │   ├── Products/                  # Product validation
│   │   │   ├── Suppliers/                 # Supplier validation
│   │   │   └── UpdatePurchaseRequest.php  # Purchase validation
│   │   ├── Sales/
│   │   │   ├── Clients/                   # Client validation
│   │   │   └── Sales/                     # Sales validation
│   │   └── Users/                         # User validation
│   └── Repositories/
│       ├── Inventary/
│       │   ├── ProductRepository.php      # Product data operations
│       │   ├── PurchaseRepository.php     # Purchase data operations
│       │   └── SupplierRepository.php     # Supplier data operations
│       ├── Sales/
│       │   ├── ClientRepository.php       # Client data operations
│       │   └── SaleRepository.php         # Sales data operations
│       └── Users/
│           └── UserRepository.php         # User data operations
├── Models/
│   ├── Client.php                         # Client model
│   ├── Product.php                        # Product model
│   ├── Purchase.php                       # Purchase model
│   ├── PurchaseDetail.php                 # Purchase detail model
│   ├── Sale.php                           # Sale model
│   ├── SaleDetail.php                     # Sale detail model
│   ├── Supplier.php                       # Supplier model
│   └── User.php                           # User model
└── Providers/
    └── AppServiceProvider.php

resources/
└── views/
    ├── pdf/                               # PDF report templates
    │   ├── purchases-by-supplier-report.blade.php
    │   ├── sales-report.blade.php
    │   └── stock-report.blade.php
    ├── swagger-ui.blade.php               # Swagger documentation
    └── welcome.blade.php                  # Welcome page
```

## System Modules

### 🔐 Authentication Module
- User registration and login
- Token-based authentication with Sanctum
- Role-based access control (Admin/Vendedor)
- Password management and security

### 📦 Inventory Module
- **Products**: Full CRUD operations for product management
- **Suppliers**: Supplier information and relationship management
- **Purchases**: Purchase orders and supplier transactions
- **Stock Management**: Inventory tracking and stock levels

### 💰 Sales Module
- **Clients**: Customer information and relationship management
- **Sales**: Sales processing and order management
- **Sales Details**: Detailed sales tracking and history

### 📊 Reporting Module
- **Sales Reports**: PDF generation for sales analytics
- **Purchase Reports**: Supplier-based purchase analysis
- **Stock Reports**: Current inventory status and levels
- **Custom Reports**: Flexible reporting capabilities

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
5. Regenerate Swagger docs: `php artisan swagger:update`

### Swagger Documentation

1. **Explore the API with Swagger UI:**
   - Visit `http://localhost:8000/api/documentation`
   - Click "login with credentials" 
   - **Admin**: `h.ezequiel.z.campos@codecastle.com` / `admin123`
   - **Vendedor**: `vendedor@codecastle.com` / `vendedor123`
   - Test any endpoint directly from the browser




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

## Repository

This project is hosted at: [https://github.com/kyxent-Immortal-Dev/Backend-Code-Castle-technical-test](https://github.com/kyxent-Immortal-Dev/Backend-Code-Castle-technical-test)

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
