<?php

namespace Gravitycar\src\Fields;

class PasswordField extends FieldBase
{
    protected string $type = 'Password';
    protected string $DBType = 'VARCHAR(255)';
    protected int $maxLength = 255;
    protected mixed $defaultValue = null;
    protected bool $isDBField = true;
    protected bool $required = true;

    protected bool $nullable = false;
}