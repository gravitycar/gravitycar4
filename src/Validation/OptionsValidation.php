<?php

namespace Gravitycar\src\Validation;

use Gravitycar\src\Fields\FieldBase;

class OptionsValidation extends ValidationRuleBase
{
    protected string $name = 'Options';
    protected string $errorMessage = 'Field {fieldName} value is not in the available options. Provided value: {value}';

    public function validate(mixed $testTestValue, FieldBase $field): bool
    {
        $this->testValue = $testTestValue;
        if ($testTestValue === null || $testTestValue === '') {
            return true;
        }

        if (method_exists($field, 'getOptions')) {
            $options = $field->getOptions();
            return in_array($testTestValue, array_keys($options), true);
        }

        return true;
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
        return "function(value, field) { if (!value) return true; if (field.getOptions && typeof field.getOptions === 'function') { var options = field.getOptions(); return Object.keys(options).includes(value); } return true; }";
    }
}