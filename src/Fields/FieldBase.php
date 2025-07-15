<?php

namespace Gravitycar\src\Fields;

use Gravitycar\exceptions\GCException;
use Gravitycar\src\Validation\ValidationRuleBase;
use Gravitycar\lib\DBConnector;
use Gravitycar\src\Validation\ValidationRuleFactory;

abstract class FieldBase
{
    public string $name = '';
    protected string $type;
    protected string $DBType;
    protected int $maxLength;
    protected mixed $defaultValue;
    protected array $validationRules = [];
    protected array $whitelist = [];
    protected array $blacklist = [];
    protected mixed $value;
    protected bool $isDBField = true;
    protected bool $required;
    protected array $validationFailures = [];
    protected mixed $originalValue; // Added missing property
    protected string $graviton;
    protected string $table;
    protected bool $nullable = true;
    protected bool $autoincrement = false;
    protected string $label = '';

    protected ValidationRuleFactory $validationRuleFactory;

    public function __construct()
    {
        $this->validationRuleFactory = new ValidationRuleFactory();
    }


    public function getName(): string
    {
        return $this->name;
    }


    public function getType(): string
    {
        return $this->type;
    }


    public function getMaxLength(): string
    {
        return $this->maxLength;
    }


    public function getNullable(): bool
    {
        return $this->nullable;
    }

    public function getWhitelist(): array
    {
        return $this->whitelist;
    }

    public function getBlacklist(): array
    {
        return $this->blacklist;
    }

    public function getIsDBField(): bool
    {
        return $this->isDBField;
    }

    public function getRequired(): bool
    {
        return $this->required;
    }

    public function getValidationFailures(): array
    {
        return $this->validationFailures;
    }

    public function getOriginalValue(): mixed
    {
        return $this->originalValue;
    }

    public function getValidationRules(): array
    {
        return $this->validationRules;
    }

    public function getDBType(): string
    {
        return $this->DBType;
    }

    public function getSigned(): bool
    {
        // Default to true if the property doesn't exist
        return property_exists($this, 'signed') ? $this->signed : true;
    }


    public function getAutoincrement(): bool
    {
        return $this->autoincrement;
    }


    public function getDefaultValue(): mixed
    {
        if (isset($this->defaultValue)) {
            return $this->defaultValue;
        }

        if ($this->nullable !== true) {
            return '';
        }

        return null;
    }


    public function collectRecordTypeData(string $recordType, string $table): void
    {
        $this->graviton = $recordType;
        $this->table = $table;
    }

    public function get(): mixed
    {
        if (property_exists($this, 'value') && isset($this->value)) {
            return $this->value;
        }
        return $this->getDefaultValue();
    }

    public function getValueForAPI(): string
    {
        return htmlspecialchars_decode($this->value);
    }

    public function setValueFromDB(mixed $value): void
    {
        $this->value = $value;
    }

    public function setOriginalValue(mixed $value): void // Added missing method
    {
        $this->originalValue = $value;
    }

    public function set(mixed $value): void
    {
        $this->validationFailures = [];

        foreach ($this->validationRules as $rule) {
            if (!$rule->validate($value, $this)) {
                $this->validationFailures[] = $rule->getFormattedErrorMessage($this);
            }
        }

        if (empty($this->validationFailures)) {
            $this->value = $value;
        }
    }

    public function addValidationRule(ValidationRuleBase $rule): void
    {
        $this->validationRules[] = $rule;
    }

    public function validationFailed(): bool
    {
        return !empty($this->validationFailures);
    }

    public function hasChanged(): bool // Added missing method
    {
        return $this->value !== $this->originalValue;
    }

    public function ingestFieldDefinitions(array $fieldDefs): void
    {
        $this->setupValidationRules($fieldDefs);
        unset($fieldDefs['validationRules']);
        foreach ($fieldDefs as $property => $value) {
            if ($this->validateFieldDefPropertyNameAndValue($property, $value)) {
                $this->$property = $value;
            }
        }
    }

    public function setupValidationRules(array $fieldDefs): void
    {
        if (!isset($fieldDefs['validationRules']) || !is_array($fieldDefs['validationRules'])) {
            return;
        }

        foreach ($fieldDefs['validationRules'] as $ruleName) {
            $this->addValidationRule($this->validationRuleFactory->getRuleByName($ruleName));
        }
    }

    public function validateFieldDefPropertyNameAndValue(string $property, mixed $value): bool
    {
        if (!property_exists($this, $property)) {
            throw new GCException("Unknown property '$property' in field definition for field '{$this->name}'");
        }

        if (gettype($value) != gettype($this->$property)) {
            $expectedType = gettype($this->$property);
            $actualType = gettype($value);
            throw new GCException("Type mismatch for property '$property' in field definition for field '{$this->name}': expected '$expectedType', got '$actualType'");
        }
        return true;
    }
}