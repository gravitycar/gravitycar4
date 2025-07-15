<?php

namespace Gravitycar\src\Validation;

use Gravitycar\exceptions\GCException;

class ValidationRuleFactory
{
    public function getRuleByName(string $ruleName): ValidationRuleBase
    {
        $className = 'Gravitycar\src\Validation\\' . $ruleName . 'Validation';

        if (!class_exists($className)) {
            throw new GCException("Validation rule class '$className' does not exist");
        }

        return new $className();
    }
}