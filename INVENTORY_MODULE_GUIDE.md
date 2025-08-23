# 🏪 Módulo de Inventario - SystemInventary

## 🎯 **Descripción General**

El **Módulo de Inventario** es un sistema completo para la gestión de productos, proveedores y compras. Está diseñado con arquitectura limpia, siguiendo las mejores prácticas de Laravel y utilizando el patrón Repository junto con FormRequest para validaciones robustas.

## 🗄️ **Modelos del Sistema**

### 1. **Product (Producto)**
**Ubicación:** `app/Models/Product.php`

**Campos:**
- `id` - Identificador único
- `name` - Nombre del producto (255 caracteres, único)
- `description` - Descripción detallada (opcional, 1000 caracteres)
- `unit_price` - Precio unitario (decimal 10,2)
- `stock` - Cantidad disponible en inventario (entero)
- `is_active` - Estado activo/inactivo (boolean)
- `created_at`, `updated_at` - Timestamps

**Relaciones:**
- `purchaseDetails()` - HasMany con PurchaseDetail

**Métodos Principales:**
```php
// Gestión de stock
$product->increaseStock(10);        // Aumenta stock
$product->decreaseStock(5);         // Disminuye stock
$product->hasStock(3);              // Verifica disponibilidad

// Scopes útiles
Product::active();                  // Solo productos activos
Product::inStock();                 // Solo con stock disponible
Product::lowStock();                // Stock bajo (< 10)

// Atributos calculados
$product->inventory_value;          // Valor total del inventario
$product->formatted_price;          // Precio formateado
$product->stock_status;             // Estado del stock
```

### 2. **Supplier (Proveedor)**
**Ubicación:** `app/Models/Supplier.php`

**Campos:**
- `id` - Identificador único
- `name` - Nombre del proveedor (255 caracteres)
- `email` - Email de contacto (255 caracteres, único)
- `phone` - Teléfono (20 caracteres, opcional)
- `address` - Dirección (opcional, 500 caracteres)
- `is_active` - Estado activo/inactivo (boolean)
- `created_at`, `updated_at` - Timestamps

**Relaciones:**
- `purchases()` - HasMany con Purchase

**Métodos Principales:**
```php
// Información del proveedor
$supplier->getTotalPurchases();     // Total de compras
$supplier->getTotalAmount();        // Monto total de compras
$supplier->getLastPurchase();       // Última compra

// Scopes útiles
Supplier::active();                 // Solo proveedores activos

// Atributos formateados
$supplier->formatted_phone;         // Teléfono formateado
$supplier->summary;                 // Resumen completo
```

### 3. **Purchase (Compra)**
**Ubicación:** `app/Models/Purchase.php`

**Campos:**
- `id` - Identificador único
- `supplier_id` - ID del proveedor (foreign key)
- `user_id` - ID del usuario que registra (foreign key)
- `purchase_date` - Fecha de compra (date)
- `total_amount` - Monto total (decimal 12,2)
- `status` - Estado: pending, completed, cancelled
- `notes` - Notas adicionales (opcional, 1000 caracteres)
- `created_at`, `updated_at` - Timestamps

**Estados:**
- `pending` - Pendiente
- `completed` - Completada
- `cancelled` - Cancelada

**Relaciones:**
- `supplier()` - BelongsTo con Supplier
- `user()` - BelongsTo con User
- `details()` - HasMany con PurchaseDetail

**Métodos Principales:**
```php
// Gestión de estados
$purchase->markAsCompleted();       // Marca como completada
$purchase->markAsCancelled();       // Marca como cancelada

// Verificaciones
$purchase->isPending();             // ¿Está pendiente?
$purchase->isCompleted();           // ¿Está completada?

// Cálculos
$purchase->calculateTotal();        // Calcula total
$purchase->getTotalItems();         // Total de productos

// Scopes útiles
Purchase::pending();                // Solo pendientes
Purchase::completed();              // Solo completadas
Purchase::dateRange($start, $end); // Por rango de fechas
```

### 4. **PurchaseDetail (Detalle de Compra)**
**Ubicación:** `app/Models/PurchaseDetail.php`

**Campos:**
- `id` - Identificador único
- `purchase_id` - ID de la compra (foreign key)
- `product_id` - ID del producto (foreign key)
- `quantity` - Cantidad comprada (entero)
- `purchase_price` - Precio de compra unitario (decimal 10,2)
- `subtotal` - Subtotal de la línea (decimal 12,2)
- `created_at`, `updated_at` - Timestamps

**Relaciones:**
- `purchase()` - BelongsTo con Purchase
- `product()` - BelongsTo con Product

**Características Especiales:**
- **Cálculo automático de subtotal** - Se calcula automáticamente antes de guardar
- **Índice único compuesto** - Evita productos duplicados en la misma compra

## 🏗️ **Arquitectura del Sistema**

### **Patrón Repository**
Cada modelo tiene su repositorio correspondiente:

1. **ProductRepository** - Gestión de productos e inventario
2. **SupplierRepository** - Gestión de proveedores
3. **PurchaseRepository** - Gestión de compras y stock

### **FormRequest para Validaciones**
Validaciones robustas y organizadas:

1. **StoreProductRequest** - Crear productos
2. **UpdateProductRequest** - Actualizar productos
3. **SearchProductRequest** - Buscar productos
4. **StoreSupplierRequest** - Crear proveedores
5. **UpdateSupplierRequest** - Actualizar proveedores
6. **StorePurchaseRequest** - Registrar compras

### **Controladores Organizados**
1. **ProductController** - CRUD completo de productos
2. **SupplierController** - CRUD completo de proveedores
3. **PurchaseController** - Gestión de compras y stock

## 🚀 **Funcionalidades Principales**

### **Gestión de Productos**
- ✅ Crear, leer, actualizar y eliminar productos
- ✅ Búsqueda avanzada con múltiples criterios
- ✅ Gestión de stock automática
- ✅ Productos con stock bajo y sin stock
- ✅ Estadísticas del inventario
- ✅ Filtros por precio, estado y disponibilidad

### **Gestión de Proveedores**
- ✅ CRUD completo de proveedores
- ✅ Validación de email único
- ✅ Formateo automático de teléfonos
- ✅ Estadísticas de compras por proveedor
- ✅ Proveedores activos/inactivos

### **Sistema de Compras**
- ✅ Registro de compras con múltiples productos
- ✅ **Actualización automática de stock** al completar compras
- ✅ Estados de compra (pendiente, completada, cancelada)
- ✅ Validaciones complejas (productos duplicados, proveedores activos)
- ✅ Cálculo automático de totales
- ✅ Historial completo de compras

### **Gestión de Stock**
- ✅ **Aumento automático** al completar compras
- ✅ **Disminución controlada** (con validaciones)
- ✅ Alertas de stock bajo
- ✅ Valor total del inventario
- ✅ Trazabilidad completa

## 📊 **Endpoints de la API**

### **Productos**
```
GET    /api/products              - Listar productos
POST   /api/products              - Crear producto
GET    /api/products/{id}         - Ver producto
PUT    /api/products/{id}         - Actualizar producto
DELETE /api/products/{id}         - Eliminar producto
PATCH  /api/products/{id}/toggle  - Cambiar estado
GET    /api/products/low-stock    - Stock bajo
GET    /api/products/out-of-stock - Sin stock
GET    /api/products/stats        - Estadísticas
GET    /api/products/price-range  - Por rango de precios
```

### **Proveedores**
```
GET    /api/suppliers             - Listar proveedores
POST   /api/suppliers             - Crear proveedor
GET    /api/suppliers/{id}        - Ver proveedor
PUT    /api/suppliers/{id}        - Actualizar proveedor
DELETE /api/suppliers/{id}        - Eliminar proveedor
PATCH  /api/suppliers/{id}/toggle - Cambiar estado
GET    /api/suppliers/active      - Proveedores activos
GET    /api/suppliers/top         - Proveedor principal
GET    /api/suppliers/by-amount   - Por monto total
GET    /api/suppliers/stats       - Estadísticas
GET    /api/suppliers/search      - Búsqueda
```

### **Compras**
```
GET    /api/purchases             - Listar compras
POST   /api/purchases             - Registrar compra
GET    /api/purchases/{id}        - Ver compra
DELETE /api/purchases/{id}        - Eliminar compra
PATCH  /api/purchases/{id}/complete - Completar compra
PATCH  /api/purchases/{id}/cancel  - Cancelar compra
GET    /api/purchases/status/{status} - Por estado
GET    /api/purchases/date-range  - Por rango de fechas
GET    /api/purchases/supplier/{id} - Por proveedor
GET    /api/purchases/stats       - Estadísticas
GET    /api/purchases/monthly     - Totales mensuales
GET    /api/purchases/top-products - Productos más comprados
```

## 🔒 **Sistema de Autorización**

### **Middleware Implementados**
- `admin` - Solo usuarios con rol admin
- `auth` - Usuarios autenticados

### **Permisos por Endpoint**
- **Crear/Actualizar/Eliminar** - Solo admin
- **Ver/Listar/Buscar** - Usuarios autenticados
- **Completar/Cancelar compras** - Solo admin

## 📝 **Ejemplos de Uso**

### **Registrar una Compra**
```php
// POST /api/purchases
{
    "supplier_id": 1,
    "purchase_date": "2024-01-15",
    "notes": "Compra mensual de productos",
    "details": [
        {
            "product_id": 1,
            "quantity": 50,
            "purchase_price": 10.50
        },
        {
            "product_id": 2,
            "quantity": 30,
            "purchase_price": 15.75
        }
    ]
}
```

### **Completar una Compra (Actualiza Stock)**
```php
// PATCH /api/purchases/{id}/complete
// Esto automáticamente:
// 1. Aumenta el stock de cada producto
// 2. Marca la compra como completada
// 3. Actualiza el estado
```

### **Buscar Productos**
```php
// GET /api/products?name=laptop&min_price=500&in_stock=true
// Filtros disponibles:
// - name: Búsqueda por nombre
// - min_price/max_price: Rango de precios
// - in_stock: Solo con stock disponible
// - low_stock: Solo con stock bajo
// - is_active: Solo activos
```

## 🎨 **Características Técnicas**

### **Base de Datos**
- ✅ **Migraciones optimizadas** con índices para rendimiento
- ✅ **Foreign keys** con restricciones apropiadas
- ✅ **Índices compuestos** para evitar duplicados
- ✅ **Comentarios descriptivos** en cada campo

### **Validaciones**
- ✅ **Validaciones complejas** con reglas personalizadas
- ✅ **Mensajes en español** para mejor UX
- ✅ **Validaciones de negocio** (proveedores activos, productos activos)
- ✅ **Preparación automática** de datos

### **Seguridad**
- ✅ **Autorización por roles** (admin vs usuario)
- ✅ **Validación de datos** en cada request
- ✅ **Transacciones de base de datos** para operaciones críticas
- ✅ **Middleware de autenticación** en todas las rutas

### **Rendimiento**
- ✅ **Eager loading** de relaciones
- ✅ **Índices de base de datos** optimizados
- ✅ **Paginación** para listas grandes
- ✅ **Scopes reutilizables** para consultas comunes

## 🔧 **Configuración y Personalización**

### **Variables de Entorno**
```env
# Configuración de base de datos
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=system_inventory
DB_USERNAME=root
DB_PASSWORD=
```

### **Personalización de Validaciones**
```php
// En cualquier FormRequest
public function rules(): array
{
    return [
        'custom_field' => 'required|string|max:255',
        // ... más reglas
    ];
}

public function messages(): array
{
    return [
        'custom_field.required' => 'Mensaje personalizado',
        // ... más mensajes
    ];
}
```

### **Agregar Nuevos Campos**
1. **Crear migración** para el nuevo campo
2. **Actualizar modelo** con fillable y casts
3. **Actualizar FormRequest** con validaciones
4. **Actualizar controlador** si es necesario

## 🚨 **Consideraciones Importantes**

### **Gestión de Stock**
- ⚠️ **Solo se actualiza al completar compras**
- ⚠️ **No se puede eliminar productos con historial de compras**
- ⚠️ **Stock no puede ser negativo**

### **Integridad de Datos**
- ⚠️ **No se pueden eliminar proveedores con compras**
- ⚠️ **No se pueden eliminar compras completadas**
- ⚠️ **Validación de productos duplicados en compras**

### **Rendimiento**
- ⚠️ **Usar paginación para listas grandes**
- ⚠️ **Implementar caché para estadísticas frecuentes**
- ⚠️ **Monitorear consultas N+1**

## 🎉 **¡Listo para Usar!**

El **Módulo de Inventario** está completamente implementado con:

- ✅ **Arquitectura limpia** y mantenible
- ✅ **Validaciones robustas** con FormRequest
- ✅ **Gestión automática de stock** al completar compras
- ✅ **Sistema de autorización** por roles
- ✅ **API REST completa** y documentada
- ✅ **Base de datos optimizada** con índices
- ✅ **Código comentado** y siguiendo mejores prácticas
- ✅ **Manejo de errores** consistente
- ✅ **Respuestas JSON estandarizadas**

¡Tu sistema de inventario está listo para manejar productos, proveedores y compras de manera profesional y eficiente! 🚀 