<?php

namespace Gravitycar\src\Fields;

class IDField extends FieldBase
{
    protected string $type = 'ID';
    protected string $DBType = 'CHAR(36)';
    protected int $maxLength = 36;
    protected mixed $defaultValue = null;
    protected bool $isDBField = true;
    protected bool $required = true;

    protected bool $nullable = false;
}