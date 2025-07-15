<?php

namespace Gravitycar\src\Validation;

use Gravitycar\src\Fields\FieldBase;

class CurrencyValidation extends ValidationRuleBase
{
    protected string $name = 'Currency';
    protected string $errorMessage = 'Field {fieldName} must be a valid currency amount. Provided value: {value}';

    public function validate(mixed $testTestValue, FieldBase $field): bool
    {
        $this->testValue = $testTestValue;
        if ($testTestValue === null || $testTestValue === '') {
            return true;
        }

        $cleaned = preg_replace('/[^0-9.-]/', '', $testTestValue);
        return filter_var($cleaned, FILTER_VALIDATE_FLOAT) !== false && $cleaned >= 0;
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
        return "function(value) { if (!value && value !== 0) return true; var cleaned = value.toString().replace(/[^0-9.-]/g, ''); return !isNaN(parseFloat(cleaned)) && parseFloat(cleaned) >= 0; }";
    }
}