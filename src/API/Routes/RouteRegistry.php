<?php

namespace Gravitycar\src\API\Routes;

use Exception;
use Gravitycar\exceptions\GCException;
use Gravitycar\src\API\BaseAPIController;
use Gravitycar\src\GCFoundation;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;

class RouteRegistry
{
    private array $routes = [];
    private array $apiClasses = [];
    private array $registry = [];
    private GCFoundation $app;
    private string $registryFilePath = 'src/API/registered_routes.php';

    public function __construct()
    {
        $this->app = GCFoundation::getInstance();
    }

    public function addRoute(Routes $route): void
    {
        $this->routes[] = $route;
    }

    public function addAPIClass(BaseAPIController $apiClass): void
    {
        $this->apiClasses[] = $apiClass;
    }

    /**
     * @throws GCException
     */
    public function collectAPIClasses(): array
    {
        $searchPaths = [
            'src/API/',
            'Gravitons/'
        ];

        foreach ($searchPaths as $searchPath) {
            if (!is_dir($searchPath)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($searchPath)
            );

            foreach ($iterator as $file) {
                if ($file->isFile() &&
                    preg_match('/.*APIController\.php$/', $file->getFilename())) {

                    $filePath = $file->getPathname();

                    try {
                        require_once $filePath;

                        // Extract class name from file path
                        $className = $this->extractClassNameFromFile($filePath);

                        if (!class_exists($className)) {
                            throw new GCException("Class $className not found in file $filePath");
                        }

                        $reflection = new ReflectionClass($className);

                        if (!$reflection->isSubclassOf(BaseAPIController::class)) {
                            throw new GCException("Class $className does not extend BaseAPIController");
                        }

                        $apiClassInstance = new $className();
                        $this->addAPIClass($apiClassInstance);

                    } catch (Exception $e) {
                        throw GCException::convert($e);
                    }
                }
            }
        }

        return $this->apiClasses;
    }

    private function extractClassNameFromFile(string $filePath): string
    {
        $content = file_get_contents($filePath);

        // Extract namespace
        preg_match('/namespace\s+([^;]+);/', $content, $namespaceMatches);
        $namespace = $namespaceMatches[1] ?? '';

        // Extract class name
        preg_match('/class\s+(\w+)/', $content, $classMatches);
        $className = $classMatches[1] ?? '';

        return $namespace ? $namespace . '\\' . $className : $className;
    }

    public function registerRoutes(): array
    {
        foreach ($this->apiClasses as $apiClass) {
            if (method_exists($apiClass, 'registerRoutes')) {
                $routes = $apiClass->registerRoutes();

                foreach ($routes as $routeData) {
                    $route = new Routes(
                        $routeData['path'],
                        $routeData['method'],
                        get_class($apiClass),
                        $routeData['handler']
                    );

                    $this->addRoute($route);
                }
            }
        }

        return $this->routes;
    }

    /**
     * @throws GCException
     */
    public function buildRegistry(): bool
    {
        $this->collectAPIClasses();
        $this->registerRoutes();

        foreach ($this->routes as $route) {
            if (!isset($this->registry[$route->method])) {
                $this->registry[$route->method] = [];
            }

            if (!isset($this->registry[$route->method][$route->length])) {
                $this->registry[$route->method][$route->length] = [];
            }

            $this->registry[$route->method][$route->length][$route->path] = $route->formatForRegistry();
        }

        return $this->storeRegistry();
    }

    public function storeRegistry(): bool
    {
        $registryDir = 'src/API/';

        if (!is_dir($registryDir)) {
            mkdir($registryDir, 0755, true);
        }

        $filePath = $registryDir . 'registered_routes.php';
        $content = "<?php\n\nreturn " . var_export($this->registry, true) . ";\n";

        return file_put_contents($filePath, $content) !== false;
    }

    /**
     * @throws GCException
     */
    public function loadRegistry(): array
    {
        if (!file_exists($this->registryFilePath)) {
            $this->collectAPIClasses();
            $this->buildRegistry();
            $this->storeRegistry();
        }

        $this->registry = require_once $this->registryFilePath;
        return $this->registry;
    }


    /**
     * @throws GCException
     */
    public function rebuildRegistry(): bool
    {
        $this->registry = [];
        $this->apiClasses = [];
        $this->routes = [];
        unlink($this->registryFilePath);
        return $this->buildRegistry();
    }

    /**
     * @throws GCException
     */
    public function searchRegistryForBestMatch(string $method, string $path): array
    {
        if (empty($this->registry)) {
            $this->loadRegistry();
        }

        $components = explode('/', $path);
        $length = count($components);
        $highestScore = 0;
        $highestScoringHandler = [];

        if (!isset($this->registry[$method][$length]) || empty($this->registry[$method][$length])) {
            throw new GCException("No API Controller with $length components for $method $path");
        }

        foreach ($this->registry[$method][$length] as $registeredPath => $routeData) {
            // Exact match
            if ($registeredPath === $path) {
                return [$routeData['handlerClassName'], $routeData['handlerMethod']];
            }

            $score = 1;

            foreach ($routeData['components'] as $position => $registeredComponent) {
                if (isset($components[$position]) &&
                    $registeredComponent === $components[$position]) {
                    $score += ($length - $position);
                }
            }

            if ($score > $highestScore) {
                $highestScore = $score;
                $highestScoringHandler = [$routeData['handlerClassName'], $routeData['handlerMethod']];
            } elseif ($score === $highestScore && !empty($highestScoringHandler)) {
                $this->app->log(
                    "Route scoring conflict: {$highestScoringHandler[0]}::{$highestScoringHandler[1]} " .
                    "has the same score ($score) as {$routeData['handlerClassName']}::{$routeData['handlerMethod']}", 'warning'
                );
            }
        }

        if (empty($highestScoringHandler)) {
            throw new GCException("No matching API Controller found for $method $path");
        }

        return $highestScoringHandler;
    }
}