<?php

namespace Gravitycar\src\Fields;

class EmailField extends FieldBase
{
    protected string $type = 'Email';
    protected string $DBType = 'VARCHAR(320)';
    protected int $maxLength = 320;
    protected mixed $defaultValue = null;
    protected bool $isDBField = true;
    protected bool $required = false;

    protected bool $nullable = true;
}