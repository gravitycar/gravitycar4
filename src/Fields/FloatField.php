<?php

namespace Gravitycar\src\Fields;

class FloatField extends FieldBase
{
    protected string $type = 'Float';
    protected string $DBType = 'DECIMAL(10,2)';
    protected int $maxLength = 13;
    protected mixed $defaultValue = 0.0;
    protected bool $isDBField = true;
    protected bool $required = false;
}