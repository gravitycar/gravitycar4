<?php

namespace Gravitycar\src\Validation;

use Gravitycar\src\Fields\FieldBase;

class DateTimeValidation extends ValidationRuleBase
{
    protected string $name = 'DateTime';
    protected string $errorMessage = 'Field {fieldName} must be a valid datetime in YYYY-MM-DDTHH:MM:SS format. Provided value: {value}';

    public function validate(mixed $testTestValue, FieldBase $field): bool
    {
        $this->testValue = $testTestValue;
        if ($testTestValue === null || $testTestValue === '') {
            return true;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $testTestValue)) {
            return false;
        }

        $date = \DateTime::createFromFormat('Y-m-d H:i:s', $testTestValue);
        return $date && $date->format('Y-m-d H:i:s') === $testTestValue;
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
        return "function(value) { if (!value) return true; var regex = /^\\d{4}-\\d{2}-\\d{2} \\d{2}:\\d{2}:\\d{2}$/; if (!regex.test(value)) return false; var date = new Date(value); return date instanceof Date && !isNaN(date); }";
    }
}