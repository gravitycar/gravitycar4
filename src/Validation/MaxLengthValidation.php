<?php

namespace Gravitycar\src\Validation;

use Gravitycar\src\Fields\FieldBase;

class MaxLengthValidation extends ValidationRuleBase
{
    protected string $name = 'MaxLength';
    protected string $errorMessage = 'Field {fieldName} exceeds maximum length. Provided value: {value}';

    public function validate(mixed $testTestValue, FieldBase $field): bool
    {
        $this->testValue = $testTestValue;
        if ($testTestValue === null || $testTestValue === '') {
            return true;
        }

        $maxLength = (int)$field->maxLength;
        return strlen((string)$testTestValue) <= $maxLength;
    }

    public function getFormattedErrorMessage(FieldBase $field): string
    {
        return str_replace(
            ['{fieldName}', '{value}'],
            [$field->name ?? 'Unknown', var_export($this->testValue, true)],
            $this->errorMessage
        );
    }

    public function getJavascriptValidation(): string
    {
        return "function(value, field) { if (!value) return true; return value.toString().length <= parseInt(field.maxLength); }";
    }
}