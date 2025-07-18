<?php

namespace Gravitycar\src\API;

use Gravitycar\exceptions\GCException;
use Gravitycar\src\Auth\Authentication;
use Gravitycar\src\GCFoundation;

class APIRouter
{
    private GCFoundation $app;
    private Authentication $auth;
    private array $routes = [];
    private string $method;
    private string $path;
    private array $headers;

    public function __construct()
    {
        $this->app = GCFoundation::getInstance();
        $this->auth = new Authentication();
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $this->headers = getallheaders();

        // Set JSON content type
        header('Content-Type: application/json');

        // Handle CORS
        $this->handleCORS();
    }

    private function handleCORS(): void
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

        if ($this->method === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }

    public function addRoute(string $method, string $pattern, callable $handler, bool $requireAuth = true): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => $pattern,
            'handler' => $handler,
            'requireAuth' => $requireAuth
        ];
    }

    public function run(): void
    {
        try {
            foreach ($this->routes as $route) {
                if ($this->matchRoute($route)) {
                    if ($route['requireAuth'] && !$this->validateAuth()) {
                        $this->sendError('Unauthorized', 401);
                        return;
                    }

                    $params = $this->extractParams($route['pattern']);
                    $response = call_user_func($route['handler'], $params);

                    $this->sendResponse($response);
                    return;
                }
            }

            $this->sendError('Endpoint not found', 404);
        } catch (GCException $e) {
            $this->sendError($e->getMessage(), 400);
        } catch (\Exception $e) {
            $this->sendError('Internal server error', 500);
        }
    }

    private function matchRoute(array $route): bool
    {
        if ($route['method'] !== $this->method) {
            return false;
        }

        $pattern = str_replace('/', '\/', $route['pattern']);
        $pattern = preg_replace('/\{[^}]+\}/', '([^\/]+)', $pattern);
        $pattern = '/^' . $pattern . '$/';

        return preg_match($pattern, $this->path);
    }

    private function extractParams(string $pattern): array
    {
        $patternParts = explode('/', $pattern);
        $pathParts = explode('/', $this->path);
        $params = [];

        foreach ($patternParts as $index => $part) {
            if (preg_match('/\{([^}]+)\}/', $part, $matches)) {
                $params[$matches[1]] = $pathParts[$index] ?? null;
            }
        }

        return $params;
    }

    private function validateAuth(): bool
    {
        $authHeader = $this->headers['Authorization'] ?? '';

        if (!$authHeader) {
            return $this->auth->isAuthenticated();
        }

        // Handle Bearer token if needed
        if (strpos($authHeader, 'Bearer ') === 0) {
            $token = substr($authHeader, 7);
            // Implement token validation logic
            return $this->validateToken($token);
        }

        return $this->auth->isAuthenticated();
    }

    private function validateToken(string $token): bool
    {
        // Implement JWT or custom token validation
        return true; // Placeholder
    }

    private function sendResponse($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        echo json_encode([
            'success' => true,
            'data' => $data,
            'timestamp' => date('c')
        ]);
    }

    private function sendError(string $message, int $statusCode = 400): void
    {
        http_response_code($statusCode);
        echo json_encode([
            'success' => false,
            'error' => $message,
            'timestamp' => date('c')
        ]);
    }

    public function getRequestData(): array
    {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?? [];
    }
}