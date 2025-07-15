<?php

namespace Gravitycar\src\Validation;

use Gravitycar\src\Fields\FieldBase;

class FloatValidation extends ValidationRuleBase
{
    protected string $name = 'Float';
    protected string $errorMessage = 'Field {fieldName} must be a valid float number. Provided value: {value}';

    public function validate(mixed $testTestValue, FieldBase $field): bool
    {
        $this->testValue = $testTestValue;
        if ($testTestValue === null || $testTestValue === '') {
            return true;
        }

        return filter_var($testTestValue, FILTER_VALIDATE_FLOAT) !== false;
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
        return "function(value) { if (!value && value !== 0) return true; return !isNaN(parseFloat(value)) && isFinite(value); }";
    }
}