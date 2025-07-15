<?php

namespace Gravitycar\src\Validation;

use Gravitycar\src\Fields\FieldBase;

class LettersOnlyValidation extends ValidationRuleBase
{
    protected string $name = 'LettersOnly';
    protected string $errorMessage = 'Field {fieldName} must contain only letters. Provided value: {value}';

    public function validate(mixed $testTestValue, FieldBase $field): bool
    {
        $this->testValue = $testTestValue;
        if ($testTestValue === null || $testTestValue === '') {
            return true;
        }

        return ctype_alpha(str_replace(' ', '', $testTestValue));
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
        return "function(value) { if (!value) return true; var regex = /^[a-zA-Z\\s]*$/; return regex.test(value); }";
    }
}