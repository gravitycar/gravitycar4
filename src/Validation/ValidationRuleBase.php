<?php

namespace Gravitycar\src\Validation;

use Gravitycar\src\Fields\FieldBase;

abstract class ValidationRuleBase
{
    protected string $name;
    protected mixed $testValue;
    protected string $errorMessage;

    abstract public function validate(mixed $testTestValue, FieldBase $field): bool;

    public function setTestValue(mixed $testValue): void
    {
        $this->testValue = $testValue;
    }

    public function getFormattedErrorMessage(FieldBase $field): string
    {
        return $this->errorMessage;
    }

    public function getJavascriptValidation(): string
    {
        return '';
    }
}