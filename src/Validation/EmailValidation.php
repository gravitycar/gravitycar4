<?php

namespace Gravitycar\src\Validation;

use Gravitycar\src\Fields\FieldBase;

class EmailValidation extends ValidationRuleBase
{
    protected string $name = 'Email';
    protected string $errorMessage = 'Field {fieldName} must be a valid email address. Provided value: {value}';

    public function validate(mixed $testTestValue, FieldBase $field): bool
    {
        $this->testValue = $testTestValue;
        if ($testTestValue === null || $testTestValue === '') {
            return true;
        }

        return filter_var($testTestValue, FILTER_VALIDATE_EMAIL) !== false;
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
        return "function(value) { if (!value) return true; var regex = /^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$/; return regex.test(value); }";
    }
}