<?php

namespace Gravitycar\src\Fields;

class MultiEnumField extends EnumField
{
    protected string $type = 'MultiEnum';
    protected string $DBType = 'TEXT';
    protected int $maxLength = 1000;
    protected mixed $defaultValue = '';
    protected bool $isDBField = true;
    protected bool $required = false;
}