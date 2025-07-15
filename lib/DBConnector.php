<?php

namespace Gravitycar\lib;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Exception;
use Gravitycar\exceptions\GCException;

class DBConnector
{
    private Connection $conn;

    public function __construct(array $connectionParams)
    {
        try {
            $this->conn = $this->connect($connectionParams);
        } catch (Exception $e) {
            throw GCException::convert($e);
        }
    }

    public function getConnection(): Connection
    {
        return $this->conn;
    }

    public function connect(array $connectionParams): Connection
    {
        try {
            return DriverManager::getConnection($connectionParams);
        } catch (Exception $e) {
            throw GCException::convert($e);
        }
    }

    public function executeStatement($sql, array $params = [], array $types = []): int
    {
        try {
            return $this->conn->executeStatement($sql, $params, $types);
        } catch (Exception $e) {
            throw GCException::convert($e);
        }
    }

    public function create(string $sql, array $params = [], array $types = []): int
    {
        try {
            return $this->conn->executeStatement($sql, $params, $types);
        } catch (Exception $e) {
            throw GCException::convert($e);
        }
    }

    public function update(string $sql, array $params = [], array $types = []): int
    {
        try {
            return $this->conn->executeStatement($sql, $params, $types);
        } catch (Exception $e) {
            throw GCException::convert($e);
        }
    }

    public function delete(string $sql, array $params = [], array $types = []): int
    {
        try {
            return $this->conn->executeStatement($sql, $params, $types);
        } catch (Exception $e) {
            throw GCException::convert($e);
        }
    }

    public function fetchOne(string $sql, array $params = [], array $types = []): mixed
    {
        try {
            return $this->conn->fetchOne($sql, $params, $types);
        } catch (Exception $e) {
            throw GCException::convert($e);
        }
    }

    public function fetchAllAssociative(string $sql, array $params = [], array $types = []): array
    {
        try {
            return $this->conn->fetchAllAssociative($sql, $params, $types);
        } catch (Exception $e) {
            throw GCException::convert($e);
        }
    }


    public function sanitize(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            return array_map([$this, 'sanitize'], $value);
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return filter_var($value, FILTER_VALIDATE_INT) !== false ? $value : 0;
        }

        if (is_float($value)) {
            return filter_var($value, FILTER_VALIDATE_FLOAT) !== false ? $value : 0.0;
        }

        if (is_string($value)) {
            // Trim whitespace
            $value = trim($value);

            // Convert special characters to HTML entities
            $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

            // Remove null bytes
            return str_replace(chr(0), '', $value);
        }

        return $value;
    }
}