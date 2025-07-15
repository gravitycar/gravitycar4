<?php

namespace Gravitycar\src\Fields;

class ImageField extends FieldBase
{
    protected string $type = 'Image';
    protected string $DBType = 'VARCHAR(500)';
    protected int $maxLength = 500;
    protected mixed $defaultValue = '';
    protected bool $isDBField = true;
    protected bool $required = false;
}