<?php

namespace Gravitycar\src\Validation;

use Gravitycar\src\Fields\FieldBase;

class RequiredValidation extends ValidationRuleBase
{
    protected string $name = 'Required';
    protected string $errorMessage = 'Field {fieldName} is required and cannot be empty. Provided value: {value}';

    public function validate(mixed $testTestValue, FieldBase $field): bool
    {
        $this->testValue = $testTestValue;
        if ($testTestValue === null || $testTestValue === '') {
            return false;
        }

        if (is_array($testTestValue) && empty($testTestValue)) {
            return false;
        }

        return true;
    }

    public function getFormattedErrorMessage(FieldBase $field): string
    {
        $formattedString = str_replace(
            ['{fieldName}', '{value}'],
            [$field->name ?? 'Unknown', var_export($this->testValue, true)],
            $this->errorMessage
        );
    }

    public function getJavascriptValidation(): string
    {
        return "function(value) { return value !== null && value !== '' && !(Array.isArray(value) && value.length === 0); }";
    }
}