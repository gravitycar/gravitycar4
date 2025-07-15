<?php

namespace Gravitycar\src\Fields;

class BoolField extends FieldBase
{
    protected string $type = 'Bool';
    protected string $DBType = 'BOOLEAN';
    protected int $maxLength = 1;
    protected mixed $defaultValue = false;
    protected bool $isDBField = true;
    protected bool $required = false;

    protected bool $nullable = false;
}