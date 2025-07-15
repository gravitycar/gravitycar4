<?php

namespace Gravitycar\src\Validation;

use Gravitycar\src\Fields\FieldBase;

class IdValidation extends ValidationRuleBase
{
    protected string $name = 'Id';
    protected string $errorMessage = 'Field {fieldName} must be a valid GUID (36 characters, format: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx). Provided value: {value}';

    public function validate(mixed $testTestValue, FieldBase $field): bool
    {
        $this->testValue = $testTestValue;
        if ($testTestValue === null || $testTestValue === '') {
            return true;
        }

        // Check exact length
        if (strlen($testTestValue) !== 36) {
            return false;
        }

        // Check GUID format: 8-4-4-4-12 characters separated by hyphens
        $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';
        return preg_match($pattern, $testTestValue) === 1;
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
        return "function(value) { if (!value) return true; if (value.length !== 36) return false; var regex = /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i; return regex.test(value); }";
    }
}