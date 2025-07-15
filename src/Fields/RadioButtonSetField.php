<?php

namespace Gravitycar\src\Fields;

class RadioButtonSetField extends FieldBase
{
    protected string $type = 'RadioButtonSet';
    protected string $DBType = 'VARCHAR(100)';
    protected int $maxLength = 100;
    protected mixed $defaultValue = '';
    protected bool $isDBField = true;
    protected bool $required = false;
    protected bool $nullable = true;
}