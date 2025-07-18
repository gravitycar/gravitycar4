<?php

namespace Gravitycar\src\API;

use Doctrine\DBAL\Exception;
use Gravitycar\exceptions\GCException;
use Gravitycar\Gravitons\Graviton;
use Gravitycar\src\API\BaseAPIController;

class GravitonAPIController extends BaseAPIController
{

    public function registerRoutes(): array
    {
        return [
            [
                'method' => 'GET',
                'path' => '/api/{graviton}',
                'handler' => [$this, 'list']
            ],
            [
                'method' => 'POST',
                'path' => '/api/{graviton}',
                'handler' => [$this, 'create']
            ],
            [
                'method' => 'GET',
                'path' => '/api/{graviton}/{id}',
                'handler' => [$this, 'read']
            ],
            [
                'method' => 'PUT',
                'path' => '/api/{graviton}/{id}',
                'handler' => [$this, 'update']
            ],
            [
                'method' => 'DELETE',
                'path' => '/api/{graviton}/{id}',
                'handler' => [$this, 'delete']
            ]
        ];
    }

    public function list(array $params): array
    {
        $limit = $_GET['limit'] ?? $this->app->getConfig('graviton_list_limit');
        $offset = $_GET['offset'] ?? 0;

        /** @var Graviton $graviton */
        $graviton = new $this->gravitonClass();
        $results = $graviton->getList($limit, $offset, $search);

        return [
            'items' => array_map([$this, 'formatGravitonForAPI'], $results),
            'pagination' => [
                'limit' => (int)$limit,
                'offset' => (int)$offset,
                'total' => $graviton->getListCount($search)
            ]
        ];
    }


    /**
     * @throws Exception
     * @throws GCException
     */
    public function read(array $params): array
    {
        $id = $params['id'] ?? null;

        if (!$id) {
            throw new GCException('ID parameter required');
        }

        /** @var Graviton $graviton */
        $graviton = new $this->gravitonClass();

        if (!$graviton->retrieve($id)) {
            throw new GCException('Resource not found');
        }

        return $this->formatGravitonForAPI($graviton);
    }


    /**
     * @throws GCException
     */
    public function create(array $params): array
    {
        $router = new APIRouter();

        /** @var Graviton $graviton */
        $graviton = new $this->gravitonClass();
        $graviton->populateFromRequest($router->getRequestData());

        if (!$graviton->save()) {
            throw new GCException('Failed to create resource');
        }

        return $this->formatGravitonForAPI($graviton);
    }


    /**
     * @throws GCException
     * @throws Exception
     */
    public function update(array $params): array
    {
        $id = $params['id'] ?? null;

        if (!$id) {
            throw new GCException('ID parameter required');
        }

        $router = new APIRouter();

        /** @var Graviton $graviton */
        $graviton = new $this->gravitonClass();

        if (!$graviton->retrieve($id)) {
            throw new GCException('Resource not found');
        }

        $graviton->populateFromRequest($router->getRequestData());
        if (!$graviton->save()) {
            throw new GCException('Failed to update resource');
        }

        return $this->formatGravitonForAPI($graviton);
    }

    /**
     * @throws GCException
     * @throws Exception
     */
    public function delete(array $params): array
    {
        $id = $params['id'] ?? null;

        if (!$id) {
            throw new GCException('ID parameter required');
        }

        /** @var Graviton $graviton */
        $graviton = new $this->gravitonClass();

        if (!$graviton->retrieve($id)) {
            throw new GCException('Resource not found');
        }

        $graviton->set('deleted', 1);
        $graviton->set('date_updated', $graviton->getCurrentDateTime());

        if (!$graviton->save()) {
            throw new GCException('Failed to delete resource');
        }

        return ['message' => 'Resource deleted successfully'];
    }


    protected function formatGravitonForAPI(Graviton $graviton): array
    {
        $data = [];

        foreach ($graviton->getFields() as $fieldName => $field) {
            $data[$fieldName] = $graviton->get($fieldName);
        }

        return $data;
    }
}