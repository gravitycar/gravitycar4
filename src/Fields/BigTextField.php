<?php

namespace Gravitycar\src\Fields;

class BigTextField extends FieldBase
{
    protected string $type = 'BigText';
    protected string $DBType = 'varchar(16384)';
    protected int $maxLength = 16384;
    protected mixed $defaultValue = '';
    protected bool $isDBField = true;
    protected bool $required = false;
}