<?php

namespace Gravitycar\src\Validation;

use Gravitycar\src\Fields\FieldBase;

class BooleanValidation extends ValidationRuleBase
{
    protected string $errorMessage = "Field '{fieldName}' must be a boolean value, '{invalidValue}' provided";

    public function validate($testValue, FieldBase $field): bool
    {
        $this->testValue = $testValue;
        return is_bool($testValue) || $testValue === 0 || $testValue === 1 || $testValue === '0' || $testValue === '1';
    }

    public function getJavascriptValidation(): string
    {
        return "function(value) { 
            return typeof value === 'boolean' || value === 0 || value === 1 || value === '0' || value === '1'; 
        }";
    }

    public function getFormattedErrorMessage(FieldBase $field): string
    {
        return str_replace(
            ['{fieldName}', '{invalidValue}'],
            [$field->getName(), (string)$field->get()],
            $this->errorMessage
        );
    }
}