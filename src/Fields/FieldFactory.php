<?php

namespace Gravitycar\src\Fields;

use Gravitycar\exceptions\GCException;
use Gravitycar\Gravitons\Graviton;

class FieldFactory
{
    protected array $fieldDefinitionFiles = [];

    public function __construct()
    {
    }

    /**
     * @throws GCException
     */
    public function getFieldsDefinitionsFromFile(string $filePath): array
    {
        if (isset($this->fieldDefinitionFiles[$filePath]) && is_array($this->fieldDefinitionFiles[$filePath])) {
            return $this->fieldDefinitionFiles[$filePath];
        }

        if (!is_file($filePath)) {
            throw new GCException("File '$filePath' is not a file");
        }

        if (!is_readable($filePath)) {
            throw new GCException("File '$filePath' is not readable");
        }

        include $filePath;

        if (!isset($fieldsList) || !is_array($fieldsList)) {
            throw new GCException("File '$filePath' does not define an array named \$fieldsList");
        }

        $this->fieldDefinitionFiles[$filePath] = $fieldsList;
        return $fieldsList;
    }


    /**
     * @throws GCException
     */
    public function ingestFieldsDefinitionFiles(array $filePaths, Graviton $graviton): array
    {
        $fields = [];
        foreach ($filePaths as $filePath) {
            $fileFields = $this->ingestFieldsDefinitionFile($filePath, $graviton);
            $fields = array_merge($fields, $fileFields);
        }
        return $fields;
    }

    /**
     * @throws GCException
     */
    public function ingestFieldsDefinitionFile(string $filePath, Graviton $graviton): array
    {
        $fieldsList = $this->getFieldsDefinitionsFromFile($filePath);

        return array_map(function ($defs) use ($graviton) {
            return $this->createFieldFromDefs($defs, $graviton);
        }, $fieldsList);
    }

    /**
     * @throws GCException
     */
    public function createFieldFromDefs(array $defs, Graviton $graviton): FieldBase
    {
        $className = $this->createFieldClassNameFromDefs($defs);
        $field = new $className($graviton);
        $field->ingestFieldDefinitions($defs);
        return $field;
    }

    /**
     * @throws GCException
     */
    public function createFieldClassNameFromDefs(array $defs): string
    {
        if (!isset($defs['name'])) {
            throw new GCException("Field definition does not define a 'name'");
        }

        if (!isset($defs['type'])) {
            throw new GCException("Field definition does not define a 'type'");
        }

        $className = "Gravitycar\\src\\Fields\\{$defs['type']}Field";

        if (!class_exists($className)) {
            throw new GCException("Field class '$className' does not exist");
        }

        return $className;
    }
}