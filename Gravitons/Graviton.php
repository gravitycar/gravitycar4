<?php

namespace Gravitycar\Gravitons;

use Doctrine\DBAL\Exception;
use Gravitycar\exceptions\GCException;
use Gravitycar\lib\DBConnector;
use Gravitycar\src\Fields\FieldFactory;
use Gravitycar\src\Fields\FieldBase;
use Gravitycar\src\GCFoundation;
use Monolog\Logger;

class Graviton
{
    protected GCFoundation $app;
    protected DBConnector $db;
    protected Logger $log;
    protected array $dbRow = [];
    protected string $id;
    protected string $type;
    protected string $table;
    protected array $fields = [];
    protected bool $recordExistsInDB;

    protected string $label = '';
    protected string $labelSingular = '';
    protected array $templates = ['base'];

    protected FieldFactory $fieldFactory;

    protected array $validationFailures = [];

    public function __construct()
    {
        $this->app = GCFoundation::getInstance();
        $this->db = $this->app->getDB();
        $this->fieldFactory = new FieldFactory();
    }


    public function getName(): string
    {
        return trim($this->get("name") ?? '');
    }


    public function validationSucceeded(): bool
    {
        $this->collectValidationErrors();
        return empty($this->validationFailures);
    }


    public function getTable(): string
    {
        return $this->table;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function idExists(): bool
    {
        if (isset($this->recordExistsInDB)) {
            return $this->recordExistsInDB;
        }

        $query = "SELECT id FROM {$this->table} WHERE id = ?";
        $result = $this->db->fetchOne($query, [$this->id]);

        if ($result && count($result) > 0) {
            $this->recordExistsInDB = true;
            return true;
        }

        $this->recordExistsInDB = false;
        return false;
    }

    public function getFieldsFilePath(): string
    {
        $fieldsFilePath =  "Gravitons/{$this->type}/fields.php";
        if (!file_exists($fieldsFilePath)) {
            throw new GCException("Fields definition file for graviton $this->type not found: $fieldsFilePath");
        }
        return $fieldsFilePath;
    }

    public function getTemplatesFilePaths(): array
    {
        $filePaths = [];
        foreach ($this->templates as $template) {
            $filePath = "Gravitons/Templates/{$template}.php";
            if (file_exists($filePath)) {
                $filePaths[] = $filePath;
            } else {
                throw new GCException("Template file for graviton $this->type not found: $filePath");
            }
        }
        return $filePaths;
    }

    /**
     * @throws GCException
     */
    public function getFieldFilesPaths(): array
    {

        $filePaths = $this->getTemplatesFilePaths();
        $filePaths[] = $this->getFieldsFilePath();
        return array_filter($filePaths, function($value) {
            return $value !== null && $value !== '';
        });
    }


    /**
     * @throws GCException
     */
    public function ingestFields(): void
    {
        $this->fields = $this->fieldFactory->ingestFieldsDefinitionFiles($this->getFieldFilesPaths(), $this);
    }


    /**
     * @throws GCException
     */
    public function getFields(): array
    {
        if (empty($this->fields)) {
            $this->ingestFields();
        }
        return $this->fields;
    }


    public function getField(string $name): ?FieldBase
    {
        if (isset($this->fields[$name])) {
            return $this->fields[$name];
        }
        return null;
    }

    public function get(string $name): mixed
    {
        if (isset($this->fields[$name])) {
            return $this->fields[$name]->get();
        }
        return null;
    }


    public function set(string $name, mixed $value): void
    {
        if (isset($this->fields[$name])) {
            $this->fields[$name]->set($value);
        } else {
            throw new GCException("Field '$name' does not exist in graviton of type '{$this->type}'");
        }
    }

    public function getTableName(): string
    {
        return $this->table;
    }

    /**
     * @throws GCException
     */
    public function populateFromRequest(array $request = []): bool
    {
        // Use $_REQUEST if no request array provided
        if (empty($request)) {
            $request = $_REQUEST;
        }

        // Get all fields
        $fields = $this->getFields();

        // Loop through each field and populate from request if present
        foreach ($fields as $fieldName => $field) {
            if (array_key_exists($fieldName, $request)) {
                $field->set($request[$fieldName]);
            }
        }


        return true;
    }


    /**
     * @throws Exception
     * @throws GCException
     */
    public function retrieve(string $id = ''): bool
    {
        // Set ID if provided and current ID is empty
        if (!empty($id) && empty($this->get('id'))) {
            $this->set('id', $id);
        }

        // Check if ID field exists and has a value
        if (!isset($this->fields['id'])) {
            throw new GCException("ID field is required to retrieve {$this->getType()} record");
        }

        $idValue = $this->get('id');
        if (empty($idValue)) {
            throw new GCException("ID field value cannot be empty to retrieve {$this->getType()} record");
        }

        // Get all database fields for SELECT clause
        $fields = $this->getFields();
        $selectFields = [];

        foreach ($fields as $fieldName => $field) {
            if ($field->getIsDBField()) {
                $selectFields[] = $fieldName;
            }
        }

        // Build SELECT statement using Doctrine DBAL QueryBuilder
        $queryBuilder = $this->db->getConnection()->createQueryBuilder();

        $queryBuilder->select(implode(', ', $selectFields))
                     ->from($this->table)
                     ->where('id = :id')
                     ->setParameter('id', $this->db->sanitize($idValue));

        // Execute the query
        $result = $queryBuilder->executeQuery()->fetchAssociative();

        if ($result) {
            // Populate fields with retrieved data
            foreach ($result as $fieldName => $value) {
                $field = $this->getField($fieldName);
                if (is_a($field, 'Gravitycar\\src\\Fields\\FieldBase')) {
                    $field->set($value);
                }
                $this->dbRow[$fieldName] = $value; // Store raw DB va
            }

            $this->recordExistsInDB = true;
            return true;
        }

        $this->recordExistsInDB = false;
        return false;
    }

    /**
     * @throws GCException
     */
    public function save(): string
    {
        try {
            if ($this->idExists()) {
                return $this->update();
            } else {
                return $this->create();
            }

        } catch (\Exception $e) {
            throw GCException::convert($e);
        }
    }


    public function collectValidationErrors(): array
    {
        foreach ($this->fields as $field) {
            foreach ($field->getValidationFailures() as $failure) {
                $this->validationFailures[] = "{$field->getType()} validation error: $failure";
            }
        }
        return $this->validationFailures;
    }


    /**
     * @throws Exception
     * @throws GCException
     */
    public function create(): string
    {
        // Generate GUID for ID field
        $guid = $this->generateGUID();
        $this->set('id', $guid);

        // Get all fields that should be saved to database
        $fields = $this->getFields();
        $queryBuilder = $this->db->getConnection()->createQueryBuilder();

        $queryBuilder->insert($this->table);

        $this->getField('date_created')->set($this->getCurrentDateTime());
        $this->getField('date_updated')->set($this->getCurrentDateTime());

        foreach ($fields as $fieldName => $field) {
            if ($field->getIsDBField()) {
                $sanitizedValue = $this->db->sanitize($field->get());
                $queryBuilder->setValue($fieldName, $queryBuilder->createNamedParameter($sanitizedValue));
            }
        }

        if (!$this->validationSucceeded()) {
            $errors = implode(', ', $this->validationFailures);
            throw new GCException("Validation failed for {$this->getType()} new record {$this->get('id')}: $errors");
        }

        // Execute the query
        $queryBuilder->executeStatement();

        // Mark record as existing in DB
        $this->recordExistsInDB = true;

        return $guid;
    }

    /**
     * @throws Exception
     * @throws GCException
     */
    public function update(): string
    {
        // Check if ID field exists and has a value
        if (!isset($this->fields['id'])) {
            throw new GCException("ID field is required to update {$this->getType()} record");
        }

        $idValue = $this->fields['id']->get();
        if (empty($idValue)) {
            throw new GCException("ID field value cannot be empty to update {$this->getType()} record");
        }

        // Get all fields that should be saved to database (excluding ID)
        $fields = $this->getFields();
        $queryBuilder = $this->db->getConnection()->createQueryBuilder();

        $queryBuilder->update($this->table);

        $this->getField('date_updated')->set($this->getCurrentDateTime());

        foreach ($fields as $fieldName => $field) {
            if ($field->getIsDBField() && $fieldName !== 'id') {
                $sanitizedValue = $this->db->sanitize($field->get());
                $queryBuilder->set($fieldName, $queryBuilder->createNamedParameter($sanitizedValue));
            }
        }

        if (!$this->validationSucceeded()) {
            $errors = implode(', ', $this->collectValidationErrors());
            throw new GCException("Validation failed for {$this->getType()} record {$this->get('id')}: $errors");
        }

        $queryBuilder->where('id = :id')
                     ->setParameter('id', $this->db->sanitize($idValue));

        // Execute the query
        $queryBuilder->executeStatement();

        // Mark record as existing in DB
        $this->recordExistsInDB = true;

        return $idValue;
    }

    private function generateGUID(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }


    public function getCurrentDateTime(): string
    {
        return date('Y-m-d H:i:s');
    }
}