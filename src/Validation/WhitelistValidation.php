<?php

namespace Gravitycar\src\Validation;

use Gravitycar\src\Fields\FieldBase;

class WhitelistValidation extends ValidationRuleBase
{
    protected string $name = 'Whitelist';
    protected string $errorMessage = 'Field {fieldName} value is not in the allowed list. Provided value: {value}';

    public function validate(mixed $testTestValue, FieldBase $field): bool
    {
        $this->testValue = $testTestValue;
        if (empty($field->whitelist)) {
            return true;
        }

        return in_array($testTestValue, $field->whitelist, true);
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
        return "function(value, field) { if (!field.whitelist || field.whitelist.length === 0) return true; return field.whitelist.includes(value); }";
    }
}