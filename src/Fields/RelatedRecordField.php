<?php

namespace Gravitycar\src\Fields;

use Gravitycar\lib\DBConnector;
use Gravitycar\src\Graviton;

class RelatedRecordField extends FieldBase
{
    protected string $type = 'RelatedRecord';
    protected string $DBType = 'TEXT';
    protected int $maxLength = 36;
    protected string $relatedRecordTable = '';
    protected string $relatedRecordType = '';
    protected string $displayField = 'name';
    protected string $relatedRecordIDColumnName = 'id';
    protected bool $optional = true;

    public function getRelatedRecordType(): string
    {
        return $this->relatedRecordType;
    }

    public function getDisplayField(): string
    {
        return $this->displayField;
    }

    public function isOptional(): bool
    {
        return $this->optional;
    }

    public function setRelatedRecordTable(string $tableName): void
    {
        $this->relatedRecordTable = $tableName;
    }

    public function setRelatedRecordIDColumnName(string $columnName): void
    {
        $this->relatedRecordIDColumnName = $columnName;
    }

    public function getJoinConditions(string $fromTable, string $joinTableAlias = ''): string
    {
        if (empty($joinTableAlias)) {
            $joinTableAlias = $this->relatedRecordTable;
        }

        return "$joinTableAlias.{$this->relatedRecordIDColumnName} = $fromTable.{$this->name} and $fromTable.deleted = 0";
    }
}