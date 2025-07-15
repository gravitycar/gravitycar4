<?php

namespace Gravitycar\src\Validation;

use Gravitycar\src\Fields\FieldBase;

class BlacklistValidation extends ValidationRuleBase
{
    protected string $name = 'Blacklist';
    protected string $errorMessage = 'Field {fieldName} value is not allowed. Provided value: {value}';

    public function validate(mixed $testTestValue, FieldBase $field): bool
    {
        $this->testValue = $testTestValue;
        $blacklist = $field->getBlacklist();
        if (empty($blacklist)) {
            return true;
        }

        return !in_array($testTestValue, $blacklist, true);
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
        return "function(value, field) { if (!field.blacklist || field.blacklist.length === 0) return true; return !field.blacklist.includes(value); }";
    }
}