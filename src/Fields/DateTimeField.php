<?php

namespace Gravitycar\src\Fields;

class DateTimeField extends FieldBase
{
    protected string $type = 'DateTime';
    protected string $DBType = 'DATETIME';
    protected int $maxLength = 19;
    protected mixed $defaultValue = null;
    protected bool $isDBField = true;
    protected bool $required = false;
}