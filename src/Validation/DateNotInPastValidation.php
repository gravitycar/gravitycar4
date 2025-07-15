<?php

namespace Gravitycar\src\Validation;

use Gravitycar\src\Fields\FieldBase;

class DateNotInPastValidation extends ValidationRuleBase
{
    protected string $name = 'DateNotInPast';
    protected string $errorMessage = 'Field {fieldName} cannot be in the past. Provided value: {value}';

    public function validate(mixed $testTestValue, FieldBase $field): bool
    {
        $this->testValue = $testTestValue;
        if ($testTestValue === null || $testTestValue === '') {
            return true;
        }

        $date = \DateTime::createFromFormat('Y-m-d', $testTestValue);
        if (!$date) {
            return false;
        }

        $today = new \DateTime();
        $today->setTime(0, 0, 0);

        return $date >= $today;
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
        return "function(value) { if (!value) return true; var inputDate = new Date(value); var today = new Date(); today.setHours(0, 0, 0, 0); return inputDate >= today; }";
    }
}