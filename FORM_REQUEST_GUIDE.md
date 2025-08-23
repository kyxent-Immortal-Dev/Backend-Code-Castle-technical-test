# Guía de FormRequest y Middlewares en SystemInventary

## 🎯 FormRequest Implementados

### 1. StoreUserRequest
**Ubicación:** `app/Http/Requests/StoreUserRequest.php`

**Validaciones:**
- `name`: Requerido, string, máximo 255 caracteres
- `email`: Requerido, email válido, único en tabla users
- `password`: Requerido, mínimo 8 caracteres
- `role`: Requerido, solo 'admin' o 'vendedor'

**Autorización:** Solo usuarios con rol 'admin'

**Uso en Controlador:**
```php
public function store(StoreUserRequest $request): JsonResponse
{
    $user = $this->userRepository->create($request->validated());
    // ... resto del código
}
```

### 2. UpdateUserRequest
**Ubicación:** `app/Http/Requests/UpdateUserRequest.php`

**Validaciones:**
- `name`: Opcional, string, máximo 255 caracteres
- `email`: Opcional, email válido, único (ignora el usuario actual)
- `password`: Opcional, mínimo 8 caracteres
- `role`: Opcional, solo 'admin' o 'vendedor'
- `is_active`: Opcional, boolean

**Autorización:** Solo usuarios con rol 'admin'

**Uso en Controlador:**
```php
public function update(UpdateUserRequest $request, $id): JsonResponse
{
    $updated = $this->userRepository->update($id, $request->validated());
    // ... resto del código
}
```

### 3. SearchUserRequest
**Ubicación:** `app/Http/Requests/SearchUserRequest.php`

**Validaciones:**
- `name`: Opcional, string, máximo 255 caracteres
- `email`: Opcional, email válido
- `page`: Opcional, entero, mínimo 1
- `per_page`: Opcional, entero, mínimo 1, máximo 100

**Autorización:** Cualquier usuario autenticado

**Uso en Controlador:**
```php
public function index(SearchUserRequest $request): JsonResponse
{
    // Los datos ya están validados automáticamente
    // ... resto del código
}
```

### 4. RegisterUserRequest
**Ubicación:** `app/Http/Requests/RegisterUserRequest.php`

**Validaciones:**
- `name`: Requerido, string, máximo 255 caracteres
- `email`: Requerido, email válido, único en tabla users
- `password`: Requerido, mínimo 8 caracteres, debe ser confirmado
- `role`: Opcional, solo 'admin' o 'vendedor'

**Autorización:** Cualquier persona (público)

**Uso en Controlador:**
```php
public function register(RegisterUserRequest $request): JsonResponse
{
    $validated = $request->validated();
    // ... resto del código
}
```

### 5. LoginUserRequest
**Ubicación:** `app/Http/Requests/LoginUserRequest.php`

**Validaciones:**
- `email`: Requerido, email válido
- `password`: Requerido, string

**Autorización:** Cualquier persona (público)

**Uso en Controlador:**
```php
public function login(LoginUserRequest $request): JsonResponse
{
    $validated = $request->validated();
    // ... resto del código
}
```

## 🛡️ Middlewares Implementados

### 1. AdminMiddleware
**Ubicación:** `app/Http/Middleware/AdminMiddleware.php`
**Alias:** `admin`

**Funcionalidad:**
- Verifica que el usuario esté autenticado
- Verifica que el usuario tenga rol 'admin'
- Retorna 401 si no está autenticado
- Retorna 403 si no tiene permisos de admin

**Uso en Rutas:**
```php
Route::middleware(['auth', 'admin'])->group(function () {
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
});
```

### 2. AuthenticatedMiddleware
**Ubicación:** `app/Http/Middleware/AuthenticatedMiddleware.php`
**Alias:** `auth`

**Funcionalidad:**
- Verifica que el usuario esté autenticado
- Retorna 401 si no está autenticado

**Uso en Rutas:**
```php
Route::middleware(['auth'])->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/profile', [AuthController::class, 'profile']);
});
```

## 🚀 Beneficios de la Implementación

### 1. **Código Más Limpio**
- Los controladores ya no tienen validaciones inline
- Lógica de validación separada y reutilizable
- Código más legible y mantenible

### 2. **Validación Automática**
- Laravel maneja automáticamente los errores de validación
- Retorna automáticamente respuesta 422 con errores
- No necesitas try-catch para ValidationException

### 3. **Autorización Centralizada**
- Lógica de permisos en un solo lugar
- Fácil de modificar y mantener
- Consistente en toda la aplicación

### 4. **Mensajes Personalizados**
- Mensajes de error en español
- Fáciles de personalizar y mantener
- Mejor experiencia de usuario

### 5. **Seguridad Mejorada**
- Verificación de roles antes de procesar la petición
- Middleware de autenticación para rutas protegidas
- Prevención de acceso no autorizado

## 📝 Ejemplo de Uso Completo

### Ruta con Middleware:
```php
// routes/api.php
Route::middleware(['auth', 'admin'])->group(function () {
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{id}', [UserController::class, 'update']);
});
```

### Controlador Simplificado:
```php
public function store(StoreUserRequest $request): JsonResponse
{
    try {
        $user = $this->userRepository->create($request->validated());
        
        return response()->json([
            'success' => true,
            'data' => $user,
            'message' => 'User created successfully'
        ], 201);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error creating user: ' . $e->getMessage()
        ], 500);
    }
}
```

## 🔧 Personalización

### Agregar Nuevas Reglas de Validación:
```php
public function rules(): array
{
    return [
        'phone' => 'sometimes|string|regex:/^\+?[1-9]\d{1,14}$/',
        'birth_date' => 'sometimes|date|before:today',
    ];
}
```

### Agregar Nuevos Mensajes:
```php
public function messages(): array
{
    return [
        'phone.regex' => 'El número de teléfono debe tener un formato válido',
        'birth_date.before' => 'La fecha de nacimiento debe ser anterior a hoy',
    ];
}
```

### Modificar Lógica de Autorización:
```php
public function authorize(): bool
{
    // Ejemplo: Solo usuarios activos pueden hacer la acción
    return Auth::check() && Auth::user()->is_active;
}
```

## 🎉 ¡Listo!

Ahora tu aplicación tiene:
- ✅ Validaciones organizadas y reutilizables
- ✅ Controladores más limpios y legibles
- ✅ Autorización centralizada y segura
- ✅ Middleware para protección de rutas
- ✅ Mensajes de error personalizados en español
- ✅ Código más mantenible y escalable

¡Tu código ahora está mucho más profesional y organizado! 🚀 