<?php

namespace Gravitycar\src\Fields;

class PhoneNumberField extends FieldBase
{
    protected string $type = 'PhoneNumber';
    protected string $DBType = 'VARCHAR(20)';
    protected int $maxLength = 20;
    protected mixed $defaultValue = null;
    protected bool $isDBField = true;
    protected bool $required = false;
}