<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Constraint;

use LogicException;

class LessThanConstraint implements ConstraintInterface
{
    public static function name(): string
    {
        return 'lessThan';
    }

    /**
     * @throws LogicException
     */
    public function validate($value, array $params = [], array $allData = []): bool
    {
        if (null === $value) {
            return true;
        }

        return $this->isLessThan($value, $allData, $params);
    }

    public function getMessage(string $valueName, $value, array $acceptedValues = []): string
    {
        return sprintf('%s should be less than %s.', $valueName, implode(', ', $acceptedValues));
    }

    /**
     * @throws LogicException
     */
    public function isLessThan($valueToCompare, array $allFieldsData, array $fieldsToCompare): bool
    {
        if (empty($fieldsToCompare)) {
            throw new LogicException("The lessThan constraint is malformed. Expected: 'lessThan:fieldToCompare'.");
        }

        foreach ($fieldsToCompare as $field) {
            if (!array_key_exists($field, $allFieldsData) || null === $allFieldsData[$field]) {
                continue;
            }

            if ($allFieldsData[$field] <= $valueToCompare) {
                return false;
            }
        }

        return true;
    }
}
