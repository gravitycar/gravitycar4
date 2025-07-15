<?php

namespace Gravitycar\src\Fields;

class CurrencyField extends FieldBase
{
    protected string $type = 'Currency';
    protected string $DBType = 'DECIMAL(10,2)';
    protected int $maxLength = 13;
    protected mixed $defaultValue = 0.00;
    protected bool $isDBField = true;
    protected bool $required = false;
}