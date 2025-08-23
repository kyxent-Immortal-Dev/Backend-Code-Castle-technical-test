<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use ReflectionMethod;

class GenerateApiDocs extends Command
{
    protected $signature = 'api:docs {--format=yaml}';
    protected $description = 'Generate OpenAPI documentation automatically from routes and controllers';

    public function handle()
    {
        $this->info('Generating API documentation...');

        $routes = $this->getApiRoutes();
        $schemas = $this->getSchemas();
        
        $openApi = $this->buildOpenApiDocument($routes, $schemas);
        
        $format = $this->option('format');
        $outputPath = $format === 'json' ? 'docs/openapi.json' : 'docs/openapi.yaml';
        
        // Ensure docs directory exists
        if (!File::exists('docs')) {
            File::makeDirectory('docs');
        }
        
        if ($format === 'json') {
            File::put($outputPath, json_encode($openApi, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            File::put($outputPath, $this->arrayToYaml($openApi));
        }
        
        // Copy to L5-Swagger storage directory
        $l5SwaggerPath = storage_path('api-docs/api-docs.yaml');
        if (!File::exists(dirname($l5SwaggerPath))) {
            File::makeDirectory(dirname($l5SwaggerPath), 0755, true);
        }
        File::copy($outputPath, $l5SwaggerPath);
        
        // Copy to public storage for web access
        $publicPath = public_path('storage/api-docs.yaml');
        if (!File::exists(dirname($publicPath))) {
            File::makeDirectory(dirname($publicPath), 0755, true);
        }
        File::copy($outputPath, $publicPath);
        
        $this->info("API documentation generated successfully at: {$outputPath}");
        $this->info("Documentation copied to L5-Swagger storage: {$l5SwaggerPath}");
        $this->info("Documentation copied to public storage: {$publicPath}");
        
        return 0;
    }
    
    private function getApiRoutes()
    {
        $routes = [];
        
        foreach (Route::getRoutes() as $route) {
            if (str_starts_with($route->uri(), 'api/')) {
                $controller = $route->getController();
                $method = $route->getActionMethod();
                
                // Skip documentation and oauth routes
                if (str_contains($route->uri(), 'documentation') || 
                    str_contains($route->uri(), 'oauth2-callback')) {
                    continue;
                }
                
                if ($controller && $method) {
                    $routes[] = [
                        'uri' => $route->uri(),
                        'methods' => $route->methods(),
                        'controller' => get_class($controller),
                        'method' => $method,
                        'middleware' => $route->middleware(),
                    ];
                }
            }
        }
        
        return $routes;
    }
    
    private function getSchemas()
    {
        return [
            'User' => [
                'type' => 'object',
                'required' => ['id', 'name', 'email', 'role', 'is_active'],
                'properties' => [
                    'id' => ['type' => 'integer', 'example' => 1, 'description' => 'Unique identifier for the user'],
                    'name' => ['type' => 'string', 'example' => 'John Doe', 'description' => 'Full name of the user'],
                    'email' => ['type' => 'string', 'format' => 'email', 'example' => 'john@example.com', 'description' => 'Unique email address'],
                    'role' => ['type' => 'string', 'enum' => ['admin', 'vendedor'], 'example' => 'admin', 'description' => 'User role in the system'],
                    'is_active' => ['type' => 'boolean', 'example' => true, 'description' => 'Whether the user account is active'],
                    'email_verified_at' => ['type' => 'string', 'format' => 'date-time', 'nullable' => true, 'description' => 'Email verification timestamp'],
                    'created_at' => ['type' => 'string', 'format' => 'date-time', 'description' => 'Account creation timestamp'],
                    'updated_at' => ['type' => 'string', 'format' => 'date-time', 'description' => 'Last update timestamp'],
                ],
            ],
            'LoginRequest' => [
                'type' => 'object',
                'required' => ['email', 'password'],
                'properties' => [
                    'email' => ['type' => 'string', 'format' => 'email', 'example' => 'admin@system.com', 'description' => 'User\'s email address'],
                    'password' => ['type' => 'string', 'example' => 'password123', 'description' => 'User\'s password (minimum 8 characters)'],
                ],
            ],
            'RegisterRequest' => [
                'type' => 'object',
                'required' => ['name', 'email', 'password', 'password_confirmation'],
                'properties' => [
                    'name' => ['type' => 'string', 'example' => 'John Doe', 'description' => 'Full name of the user'],
                    'email' => ['type' => 'string', 'format' => 'email', 'example' => 'john@example.com', 'description' => 'Unique email address'],
                    'password' => ['type' => 'string', 'minLength' => 8, 'example' => 'password123', 'description' => 'Password (minimum 8 characters)'],
                    'password_confirmation' => ['type' => 'string', 'example' => 'password123', 'description' => 'Password confirmation (must match password)'],
                    'role' => ['type' => 'string', 'enum' => ['admin', 'vendedor'], 'example' => 'vendedor', 'description' => 'User role (defaults to vendedor if not specified)'],
                ],
            ],
            'CreateUserRequest' => [
                'type' => 'object',
                'required' => ['name', 'email', 'password', 'role'],
                'properties' => [
                    'name' => ['type' => 'string', 'example' => 'New User', 'description' => 'Full name of the user'],
                    'email' => ['type' => 'string', 'format' => 'email', 'example' => 'newuser@example.com', 'description' => 'Unique email address'],
                    'password' => ['type' => 'string', 'minLength' => 8, 'example' => 'password123', 'description' => 'Password (minimum 8 characters)'],
                    'role' => ['type' => 'string', 'enum' => ['admin', 'vendedor'], 'example' => 'vendedor', 'description' => 'User role in the system'],
                ],
            ],
            'UpdateUserRequest' => [
                'type' => 'object',
                'properties' => [
                    'name' => ['type' => 'string', 'example' => 'Updated Name', 'description' => 'Full name of the user'],
                    'email' => ['type' => 'string', 'format' => 'email', 'example' => 'updated@example.com', 'description' => 'Unique email address'],
                    'password' => ['type' => 'string', 'minLength' => 8, 'example' => 'newpassword123', 'description' => 'New password (minimum 8 characters)'],
                    'role' => ['type' => 'string', 'enum' => ['admin', 'vendedor'], 'example' => 'admin', 'description' => 'User role in the system'],
                    'is_active' => ['type' => 'boolean', 'example' => true, 'description' => 'Whether the user account is active'],
                ],
            ],
            'SuccessResponse' => [
                'type' => 'object',
                'properties' => [
                    'success' => ['type' => 'boolean', 'example' => true, 'description' => 'Indicates if the operation was successful'],
                    'data' => ['type' => 'object', 'description' => 'Response data (varies by endpoint)'],
                    'message' => ['type' => 'string', 'example' => 'Operation completed successfully', 'description' => 'Human-readable success message'],
                ],
            ],
            'ErrorResponse' => [
                'type' => 'object',
                'properties' => [
                    'success' => ['type' => 'boolean', 'example' => false, 'description' => 'Indicates if the operation was successful'],
                    'message' => ['type' => 'string', 'example' => 'Error message', 'description' => 'Human-readable error message'],
                    'errors' => ['type' => 'object', 'nullable' => true, 'description' => 'Detailed error information (if available)'],
                ],
            ],
            'ValidationErrorResponse' => [
                'type' => 'object',
                'properties' => [
                    'success' => ['type' => 'boolean', 'example' => false, 'description' => 'Indicates if the operation was successful'],
                    'message' => ['type' => 'string', 'example' => 'Validation errors', 'description' => 'Human-readable error message'],
                    'errors' => [
                        'type' => 'object',
                        'description' => 'Field-specific validation errors',
                        'properties' => [
                            'email' => ['type' => 'array', 'items' => ['type' => 'string'], 'example' => ['The email field is required.']],
                            'password' => ['type' => 'array', 'items' => ['type' => 'string'], 'example' => ['The password must be at least 8 characters.']],
                        ],
                    ],
                ],
            ],
        ];
    }
    
    private function buildOpenApiDocument($routes, $schemas)
    {
        $paths = [];
        
        foreach ($routes as $route) {
            $path = '/' . str_replace('api/', '', $route['uri']);
            $method = strtolower($route['methods'][0]);
            
            if (!isset($paths[$path])) {
                $paths[$path] = [];
            }
            
            $paths[$path][$method] = $this->buildPathItem($route);
        }
        
        return [
            'openapi' => '3.0.3',
            'info' => [
                'title' => 'System Inventory API',
                'description' => 'Complete API for user management with authentication and role-based access control. This API provides endpoints for user registration, authentication, and comprehensive user management with admin and vendedor roles.',
                'version' => '1.0.0',
                'contact' => [
                    'email' => 'admin@system.com',
                    'name' => 'System Administrator',
                    'url' => 'https://github.com/your-repo',
                ],
                'license' => [
                    'name' => 'MIT',
                    'url' => 'https://opensource.org/licenses/MIT',
                ],
            ],
            'servers' => [
                ['url' => 'http://localhost:8000/api', 'description' => 'Local Development Server'],
                ['url' => 'https://your-production-domain.com/api', 'description' => 'Production Server'],
            ],
            'paths' => $paths,
            'components' => [
                'securitySchemes' => [
                    'cookieAuth' => [
                        'type' => 'apiKey',
                        'in' => 'cookie',
                        'name' => 'laravel_session',
                        'description' => 'Session cookie for authentication',
                    ],
                ],
                'schemas' => $schemas,
            ],
            'tags' => [
                ['name' => 'Authentication', 'description' => 'User authentication endpoints including registration, login, logout, profile management, and session refresh'],
                ['name' => 'Users', 'description' => 'User management endpoints for administrators to create, read, update, delete, and manage user status'],
            ],
            'externalDocs' => [
                'description' => 'Find more info about this API',
                'url' => 'https://github.com/your-repo',
            ],
        ];
    }
    
    private function buildPathItem($route)
    {
        $pathItem = [
            'tags' => $this->getTags($route),
            'summary' => $this->getSummary($route),
            'description' => $this->getRouteDescription($route),
        ];
        
        // Add security if route requires authentication
        if ($this->requiresAuth($route)) {
            $pathItem['security'] = [['cookieAuth' => []]];
        }
        
        // Add request body for POST/PUT methods
        if (in_array($route['methods'][0], ['POST', 'PUT', 'PATCH'])) {
            $pathItem['requestBody'] = $this->getRequestBody($route);
        }
        
        // Add parameters for path parameters
        $pathParams = $this->getPathParameters($route);
        if (!empty($pathParams)) {
            $pathItem['parameters'] = $pathParams;
        }
        
        // Add responses
        $pathItem['responses'] = $this->getResponses($route);
        
        return $pathItem;
    }
    
    private function getTags($route)
    {
        if (str_contains($route['uri'], 'users')) {
            return ['Users'];
        }
        return ['Authentication'];
    }
    
    private function getSummary($route)
    {
        $method = $route['method'];
        $uri = $route['uri'];
        
        if (str_contains($uri, 'register')) return 'Register a new user';
        if (str_contains($uri, 'login')) return 'Login user';
        if (str_contains($uri, 'logout')) return 'Logout user';
        if (str_contains($uri, 'profile')) return 'Get user profile';
        if (str_contains($uri, 'refresh')) return 'Refresh session';
        if (str_contains($uri, 'users') && $route['methods'][0] === 'GET') return 'Get all users';
        if (str_contains($uri, 'users') && $route['methods'][0] === 'POST') return 'Create new user';
        if (str_contains($uri, 'users') && $route['methods'][0] === 'PUT') return 'Update user';
        if (str_contains($uri, 'users') && $route['methods'][0] === 'DELETE') return 'Delete user';
        if (str_contains($uri, 'toggle-status')) return 'Toggle user status';
        
        return ucfirst($method);
    }
    
    private function getRouteDescription($route)
    {
        $method = $route['method'];
        $uri = $route['uri'];
        
        if (str_contains($uri, 'register')) return 'Create a new user account with role assignment. User is automatically logged in.';
        if (str_contains($uri, 'login')) return 'Authenticate user and create session';
        if (str_contains($uri, 'logout')) return 'Logout user and invalidate session';
        if (str_contains($uri, 'profile')) return 'Get authenticated user\'s profile information';
        if (str_contains($uri, 'refresh')) return 'Regenerate session ID for security';
        if (str_contains($uri, 'users') && $route['methods'][0] === 'GET') return 'Retrieve list of all users with optional filtering and pagination';
        if (str_contains($uri, 'users') && $route['methods'][0] === 'POST') return 'Create a new user account (Admin only)';
        if (str_contains($uri, 'users') && $route['methods'][0] === 'PUT') return 'Update existing user (Admin only)';
        if (str_contains($uri, 'users') && $route['methods'][0] === 'DELETE') return 'Delete user account (Admin only)';
        if (str_contains($uri, 'toggle-status')) return 'Toggle user active/inactive status (Admin only)';
        
        return 'API endpoint';
    }
    
    private function requiresAuth($route)
    {
        return !in_array($route['method'], ['login', 'register']);
    }
    
    private function getRequestBody($route)
    {
        $method = $route['method'];
        $uri = $route['uri'];
        
        if (str_contains($uri, 'register')) {
            return [
                'required' => true,
                'content' => [
                    'application/json' => [
                        'schema' => ['$ref' => '#/components/schemas/RegisterRequest'],
                    ],
                ],
            ];
        }
        
        if (str_contains($uri, 'login')) {
            return [
                'required' => true,
                'content' => [
                    'application/json' => [
                        'schema' => ['$ref' => '#/components/schemas/LoginRequest'],
                    ],
                ],
            ];
        }
        
        if (str_contains($uri, 'users') && $route['methods'][0] === 'POST') {
            return [
                'required' => true,
                'content' => [
                    'application/json' => [
                        'schema' => ['$ref' => '#/components/schemas/CreateUserRequest'],
                    ],
                ],
            ];
        }
        
        if (str_contains($uri, 'users') && $route['methods'][0] === 'PUT') {
            return [
                'required' => true,
                'content' => [
                    'application/json' => [
                        'schema' => ['$ref' => '#/components/schemas/UpdateUserRequest'],
                    ],
                ],
            ];
        }
        
        return null;
    }
    
    private function getPathParameters($route)
    {
        $uri = $route['uri'];
        $params = [];
        
        if (preg_match('/\{(\w+)\}/', $uri, $matches)) {
            $params[] = [
                'name' => $matches[1],
                'in' => 'path',
                'description' => ucfirst($matches[1]) . ' identifier',
                'required' => true,
                'schema' => ['type' => 'integer', 'example' => 1],
            ];
        }
        
        return $params;
    }
    
    private function getResponses($route)
    {
        $responses = [];
        $method = $route['methods'][0];
        $uri = $route['uri'];
        
        // Success responses
        if ($method === 'POST' && str_contains($uri, 'register')) {
            $responses['201'] = [
                'description' => 'User registered successfully',
                'content' => [
                    'application/json' => [
                        'schema' => ['$ref' => '#/components/schemas/SuccessResponse'],
                    ],
                ],
            ];
        } elseif ($method === 'POST' && str_contains($uri, 'users')) {
            $responses['201'] = [
                'description' => 'User created successfully',
                'content' => [
                    'application/json' => [
                        'schema' => ['$ref' => '#/components/schemas/SuccessResponse'],
                    ],
                ],
            ];
        } else {
            $responses['200'] = [
                'description' => 'Success',
                'content' => [
                    'application/json' => [
                        'schema' => ['$ref' => '#/components/schemas/SuccessResponse'],
                    ],
                ],
            ];
        }
        
        // Error responses
        $responses['401'] = [
            'description' => 'Unauthenticated',
            'content' => [
                'application/json' => [
                    'schema' => ['$ref' => '#/components/schemas/ErrorResponse'],
                ],
            ],
        ];
        
        if (str_contains($uri, 'users')) {
            $responses['403'] = [
                'description' => 'Access denied. Admin role required.',
                'content' => [
                    'application/json' => [
                        'schema' => ['$ref' => '#/components/schemas/ErrorResponse'],
                    ],
                ],
            ];
            
            if (in_array($method, ['GET', 'PUT', 'DELETE', 'PATCH'])) {
                $responses['404'] = [
                    'description' => 'User not found',
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/ErrorResponse'],
                        ],
                    ],
                ];
            }
        }
        
        if (in_array($method, ['POST', 'PUT'])) {
            $responses['422'] = [
                'description' => 'Validation errors',
                'content' => [
                    'application/json' => [
                        'schema' => ['$ref' => '#/components/schemas/ValidationErrorResponse'],
                    ],
                ],
            ];
        }
        
        $responses['500'] = [
            'description' => 'Server error',
            'content' => [
                'application/json' => [
                    'schema' => ['$ref' => '#/components/schemas/ErrorResponse'],
                ],
            ],
        ];
        
        return $responses;
    }
    
    private function arrayToYaml($array, $indent = 0)
    {
        $yaml = '';
        $indentStr = str_repeat('  ', $indent);
        
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (empty($value)) {
                    $yaml .= $indentStr . $key . ": []\n";
                } elseif (array_keys($value) !== range(0, count($value) - 1)) {
                    $yaml .= $indentStr . $key . ":\n";
                    $yaml .= $this->arrayToYaml($value, $indent + 1);
                } else {
                    $yaml .= $indentStr . $key . ":\n";
                    foreach ($value as $item) {
                        if (is_array($item)) {
                            $yaml .= $indentStr . "  -\n";
                            $yaml .= $this->arrayToYaml($item, $indent + 2);
                        } else {
                            $yaml .= $indentStr . "  - " . $this->formatYamlValue($item) . "\n";
                        }
                    }
                }
            } else {
                $yaml .= $indentStr . $key . ": " . $this->formatYamlValue($value) . "\n";
            }
        }
        
        return $yaml;
    }
    
    private function formatYamlValue($value)
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_null($value)) {
            return 'null';
        }
        if (is_string($value) && (str_contains($value, ':') || str_contains($value, '{') || str_contains($value, '}') || str_contains($value, '[') || str_contains($value, ']') || str_contains($value, '&') || str_contains($value, '*') || str_contains($value, '#') || str_contains($value, '|') || str_contains($value, '-') || str_contains($value, '?') || str_contains($value, '!') || str_contains($value, '>') || str_contains($value, '%') || str_contains($value, '@') || str_contains($value, '`'))) {
            return '"' . addslashes($value) . '"';
        }
        return $value;
    }
} 