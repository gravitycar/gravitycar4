<?php

namespace Gravitycar\src\TableBuilder;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\SchemaException;
use Gravitycar\exceptions\GCException;
use Gravitycar\src\GCFoundation;
use Gravitycar\lib\DBConnector;
use Gravitycar\Gravitons\Graviton;
use Gravitycar\src\Fields\FieldBase;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;

class TableBuilder
{
    protected string $gravitonsPath = 'Gravitons';
    protected GCFoundation $app;
    protected DBConnector $db;

    public function __construct(GCFoundation $app, DBConnector $db)
    {
        $this->app = $app;
        $this->db = $db;
    }

    public function findAllGravitons(): array
    {
        $gravitons = [];
        $directories = glob($this->gravitonsPath . '/*', GLOB_ONLYDIR);

        foreach ($directories as $directory) {
            $dirName = basename($directory);
            $className = "Gravitycar\\Gravitons\\$dirName\\$dirName";

            if (!class_exists($className)) {
                $this->app->log("Class $className does not exist. Skipping.", 'debug');
                continue;
            }

            $gravitons[] = new $className($this->db);
        }

        return $gravitons;
    }

    public function buildAllTables(): void
    {
        try {
            $gravitons = $this->findAllGravitons();
        } catch (\Exception $e) {
            $this->app->log('Error finding gravitons: ' . $e->getMessage(), 'error');
            $gravitons = [];
        }

        foreach ($gravitons as $graviton) {
            try {
                $this->buildTableForGraviton($graviton);
            } catch (\Exception $e) {
                $graviton->getType();
                $this->app->log("Error building table {$graviton->getTableName()} for graviton {$graviton->getType()}: " . $e->getMessage(), 'error');
            }
        }
    }


    /**
     * @throws Exception
     */
    public function getSchema(): Schema
    {
        $schemaManager = $this->getSchemaManager();
        return $schemaManager->introspectSchema();
    }


    /**
     * @throws Exception
     */
    public function getSchemaManager(): AbstractSchemaManager
    {
        $connection = $this->db->getConnection();
        return $connection->createSchemaManager();
    }

    /**
     * @throws GCException
     * @throws SchemaException
     * @throws Exception
     */
    public function buildTableForGraviton(Graviton $graviton): void
    {
        $schemaManager = $this->getSchemaManager();
        $schema = $this->getSchema();
        $tableName = $graviton->getTableName();
        $fields = $graviton->getFields();

        // Drop table if it already exists
        if ($schema->hasTable($tableName)) {
            $schema->dropTable($tableName);
            $schemaManager->dropTable($tableName);
            // at some point, when we have data, we want to stop doing this and just skip any tables that are created.
            // continue;
        }

        $table = $schema->createTable($tableName);

        foreach ($fields as $field) {
            try {
                $this->addColumnToTable($table, $field);
            } catch (\Exception $e) {
                throw GCException::convert($e);
            }
        }

        // Execute the schema changes
        $createQueries = $this->db->getConnection()->getDatabasePlatform()->getCreateTablesSQL([$table]);
        foreach ($createQueries as $createQuery) {
            $this->app->log("Executing SQL to create table $tableName: $createQuery", 'debug');
            $this->db->executeStatement($createQuery);
        }
    }

    /**
     * @throws SchemaException
     */
    public function addColumnToTable(Table $table, FieldBase $field): void
    {
        if (!$field->getIsDBField()) {
            return;
        }

        $columnName = $field->getName();
        $columnType = $this->mapFieldTypeToDoctrineType($field);
        $options = $this->getColumnOptions($field);

        $table->addColumn($columnName, $columnType, $options);

        // Handle primary key
        if ($field->getName() === 'id') {
            $table->setPrimaryKey([$columnName]);
        }
    }


    public function getColumnOptions(FieldBase $field): array
    {
        $options = [
            'notnull' => !$field->getNullable(),
            'default' => $field->getDefaultValue()
        ];

        // Handle length for string types
        if (property_exists($field, 'maxLength') && !empty($field->getMaxLength())) {
            $options['length'] = $field->getMaxLength();
        }

        // Handle autoincrement
        if ($field->getAutoincrement()) {
            $options['autoincrement'] = true;
        }

        // Handle unsigned for numeric types
        $columnType = $this->mapFieldTypeToDoctrineType($field);
        if (!$field->getSigned() && in_array($columnType, [Types::INTEGER, Types::BIGINT, Types::SMALLINT])) {
            $options['unsigned'] = true;
        }

        return $options;
    }


    /**
     * @throws SchemaException
     * @throws Exception
     */
    public function dropTableForGraviton(Graviton $graviton): void
    {
        $schemaManager = $this->getSchemaManager();
        $schema = $this->getSchema();
        $tableName = $graviton->getTableName();

        if ($schemaManager->tablesExist([$tableName])) {
            $schema->dropTable($tableName);
            $schemaManager->dropTable($tableName);
        }
    }

    protected function mapFieldTypeToDoctrineType(FieldBase $field): string
    {
        // Map field types to Doctrine DBAL types
        // This assumes the field has a getDBType() method or similar
        if (property_exists($field, 'DBType')) {
            return match (strtolower($field->getDBType())) {
                'varchar', 'text' => Types::STRING,
                'int', 'integer' => Types::INTEGER,
                'bigint' => Types::BIGINT,
                'smallint' => Types::SMALLINT,
                'decimal' => Types::DECIMAL,
                'float' => Types::FLOAT,
                'boolean', 'bool' => Types::BOOLEAN,
                'datetime' => Types::DATETIME_MUTABLE,
                'date' => Types::DATE_MUTABLE,
                'time' => Types::TIME_MUTABLE,
                'json' => Types::JSON,
                default => Types::STRING
            };
        }

        return Types::STRING;
    }

    public function buildOrSyncAllGravitonTables(): void
    {
        try {
            $gravitons = $this->findAllGravitons();
        } catch (\Exception $e) {
            $this->app->log('Error finding gravitons: ' . $e->getMessage(), 'error');
            $gravitons = [];
        }

        foreach ($gravitons as $graviton) {
            try {
                $tableName = $graviton->getTableName();
                $schemaManager = $this->getSchemaManager();

                if ($schemaManager->tablesExist([$tableName])) {
                    // Table exists, sync it
                    $this->syncTableWithGraviton($graviton);
                } else {
                    // Table does not exist, build it
                    $this->buildTableForGraviton($graviton);
                }
            } catch (\Exception $e) {
                $this->app->log("Error building or syncing table {$graviton->getTableName()} for graviton {$graviton->getType()}: " . $e->getMessage(), 'error');
            }
        }
    }

    /**
     * @throws GCException
     * @throws SchemaException
     * @throws Exception
     */
    public function syncTableWithGraviton(Graviton $graviton): void
    {
        $schemaManager = $this->getSchemaManager();
        $platform = $this->db->getConnection()->getDatabasePlatform();
        $tableName = $graviton->getTableName();

        if (!$schemaManager->tablesExist([$tableName])) {
            throw new GCException("Cannot sync table '$tableName' because it does not exist");
        }

        // Get current table schema
        $currentTable = $schemaManager->introspectTable($tableName);
        $fromSchema = new Schema([$currentTable]);

        // Create target schema from graviton fields
        $targetSchema = new Schema();
        $targetTable = $targetSchema->createTable($tableName);

        foreach ($graviton->getFields() as $field) {
            if ($field->getIsDBField()) {
                try {
                    $this->addColumnToTable($targetTable, $field);
                } catch (\Exception $e) {
                    throw GCException::convert($e);
                }
            }
        }

        // Compare schemas and generate diff
        $comparator = $schemaManager->createComparator();
        $schemaDiff = $comparator->compareSchemas($fromSchema, $targetSchema);

        // Generate and execute ALTER TABLE queries
        foreach ($schemaDiff->toSql($platform) as $query) {
            $this->db->executeStatement($query);
        }
    }
}