<?php

namespace Gravitycar\src\Validation;

use Gravitycar\src\Fields\FieldBase;

class PasswordValidation extends ValidationRuleBase
{
    protected string $name = 'Password';
    protected string $errorMessage = 'Field {fieldName} must be at least 8 characters long and cannot be empty. Provided value: {value}';

    public function validate(mixed $testTestValue, FieldBase $field): bool
    {
        $this->testValue = $testTestValue;
        if ($testTestValue === null || $testTestValue === '') {
            return false;
        }

        return strlen($testTestValue) >= 8;
    }

    public function getFormattedErrorMessage(FieldBase $field): string
    {
        return str_replace(
            ['{fieldName}', '{value}'],
            [$field->name ?? 'Unknown', '[HIDDEN]'],
            $this->errorMessage
        );
    }

    public function getJavascriptValidation(): string
    {
        return "function(value) { return value && value.length >= 8; }";
    }
}