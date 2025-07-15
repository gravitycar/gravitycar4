<?php

namespace Gravitycar\src\Fields;

class TextField extends FieldBase
{
    protected string $type = 'Text';
    protected string $DBType = 'VARCHAR(255)';
    protected int $maxLength = 255;
    protected mixed $defaultValue = '';
    protected bool $isDBField = true;
    protected bool $required = false;
}
