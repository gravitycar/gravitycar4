<?php

namespace Gravitycar\src\Fields;

class DateField extends FieldBase
{
    protected string $type = 'Date';
    protected string $DBType = 'DATE';
    protected int $maxLength = 10;
    protected mixed $defaultValue = null;
    protected bool $isDBField = true;
    protected bool $required = false;
}