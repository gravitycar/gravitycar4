<?php

namespace Gravitycar\src\Fields;

use Gravitycar\exceptions\GCException;

class EnumField extends FieldBase
{
    protected string $type = 'Enum';
    protected string $DBType = 'VARCHAR';
    protected int $maxLength = 100;
    protected mixed $defaultValue = '';
    protected bool $isDBField = true;
    protected bool $required = false;

    protected string $optionsClass = '';

    protected string $optionsMethod = '';

    public function getOptions(): array
    {
        if ($this->optionsClass && $this->optionsMethod) {
            if (class_exists($this->optionsClass) && method_exists($this->optionsClass, $this->optionsMethod)) {
                $options =  call_user_func([$this->optionsClass, $this->optionsMethod]);
                if (is_array($options)) {
                    return $options;
                } else {
                    throw new GCException("$this->type field $this->name options method {$this->optionsClass}->{$this->optionsMethod} does not return an array. Return value: " . var_export($options, true));
                }
            } else {
                throw new GCException("$this->type field $this->name options class {$this->optionsClass} and/or options method {$this->optionsMethod} does not exist.");
            }
        }

        return [];
    }
}