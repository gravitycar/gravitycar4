<?php

namespace Gravitycar\src\Fields;

class IntegerField extends FieldBase
{
    protected string $type = 'Integer';
    protected string $DBType = 'INT';
    protected int $maxLength = 11;
    protected mixed $defaultValue = 0;
    protected bool $isDBField = true;
    protected bool $required = false;
    protected bool $signed = true; // New property to indicate if the integer is signed or unsigned
}