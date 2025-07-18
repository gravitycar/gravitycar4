<?php

namespace Gravitycar\src\API\Routes;

class Routes
{
    private string $path;
    private string $method;
    private string $handlerClassName;
    private string $handlerMethod;
    private array $components;
    private int $length;

    public function __construct(string $path, string $method, string $handlerClassName, string $handlerMethod)
    {
        $this->path = $path;
        $this->method = $method;
        $this->handlerClassName = $handlerClassName;
        $this->handlerMethod = $handlerMethod;
        $this->components = explode('/', $path);
        $this->length = count($this->components);
    }

    public function formatForRegistry(): array
    {
        return [
            'path' => $this->path,
            'components' => $this->components,
            'handlerClassName' => $this->handlerClassName,
            'handlerMethod' => $this->handlerMethod
        ];
    }
}