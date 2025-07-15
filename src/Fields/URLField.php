<?php

namespace Gravitycar\src\Fields;

class URLField extends FieldBase
{
    protected string $type = 'URL';
    protected string $DBType = 'VARCHAR';
    protected int $maxLength = 2048;
    protected mixed $defaultValue = null;
    protected bool $isDBField = true;
    protected bool $required = false;
    protected bool $nullable = true;
    protected bool $autoincrement = false;
    protected array $validationRules = ['url'];
}