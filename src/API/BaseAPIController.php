<?php

namespace Gravitycar\src\API;

use Gravitycar\exceptions\GCException;
use Gravitycar\Gravitons\Graviton;
use Gravitycar\src\Auth\Authentication;

abstract class BaseAPIController
{
    protected Authentication $auth;
    protected string $gravitonClass;

    public function __construct(string $gravitonClass)
    {
        $this->auth = new Authentication();
        $this->gravitonClass = $gravitonClass;
    }

    abstract public function registerRoutes(): array;

    /**
     * GET /api/{resource}
     */
    abstract public function list(array $params): array;

    /**
     * GET /api/{resource}/{id}
     */
    abstract public function read(array $params): array;

    /**
     * POST /api/{resource}
     */
    abstract public function create(array $params): array;

    /**
     * PUT /api/{resource}/{id}
     */
    abstract public function update(array $params): array;

    /**
     * DELETE /api/{resource}/{id}
     */
    abstract public function delete(array $params): array;

    /**
     * Format a Graviton object for API response
     */
    abstract protected function formatGravitonForAPI(Graviton $graviton): array;
}