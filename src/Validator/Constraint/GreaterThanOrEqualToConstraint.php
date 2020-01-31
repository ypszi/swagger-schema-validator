<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Constraint;

use LogicException;

class GreaterThanOrEqualToConstraint implements ConstraintInterface
{
    public static function name(): string
    {
        return 'greaterThanOrEqualTo';
    }

    /**
     * @throws LogicException
     */
    public function validate($value, array $params = [], array $allData = []): bool
    {
        if (null === $value) {
            return true;
        }

        return $this->isGreaterThanOrEqualTo($value, $allData, $params);
    }

    public function getMessage(string $valueName, $value, array $acceptedValues = []): string
    {
        return sprintf('%s should be greater than or equal to %s.', $valueName, implode(', ', $acceptedValues));
    }

    /**
     * @throws LogicException
     */
    public function isGreaterThanOrEqualTo($valueToCompare, array $allFieldsData, array $fieldsToCompare): bool
    {
        if (empty($fieldsToCompare)) {
            throw new LogicException(
                "The greaterThanOrEqualTo constraint is malformed. Expected: 'greaterThanOrEqualTo:fieldToCompare'."
            );
        }

        foreach ($fieldsToCompare as $field) {
            if (!array_key_exists($field, $allFieldsData) || null === $allFieldsData[$field]) {
                continue;
            }

            if ($allFieldsData[$field] > $valueToCompare) {
                return false;
            }
        }

        return true;
    }
}
