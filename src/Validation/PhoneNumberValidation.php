<?php

namespace Gravitycar\src\Validation;

use Gravitycar\src\Fields\FieldBase;

class PhoneNumberValidation extends ValidationRuleBase
{
    protected string $name = 'PhoneNumber';
    protected string $errorMessage = 'Field {fieldName} must be a valid phone number. Provided value: {value}';

    public function validate(mixed $testTestValue, FieldBase $field): bool
    {
        $this->testValue = $testTestValue;
        if ($testTestValue === null || $testTestValue === '') {
            return true;
        }

        $cleaned = preg_replace('/[^0-9]/', '', $testTestValue);
        return strlen($cleaned) >= 10 && strlen($cleaned) <= 15;
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
        return "function(value) { if (!value) return true; var cleaned = value.replace(/[^0-9]/g, ''); return cleaned.length >= 10 && cleaned.length <= 15; }";
    }
}