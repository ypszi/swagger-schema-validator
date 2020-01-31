<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Constraint;

use LogicException;

class GreaterThanConstraint implements ConstraintInterface
{
    public static function name(): string
    {
        return 'greaterThan';
    }

    /**
     * @throws LogicException
     */
    public function validate($value, array $params = [], array $allData = []): bool
    {
        if (null === $value) {
            return true;
        }

        return $this->isGreaterThan($value, $allData, $params);
    }

    public function getMessage(string $valueName, $value, array $acceptedValues = []): string
    {
        return sprintf('%s should be greater than %s.', $valueName, implode(', ', $acceptedValues));
    }

    /**
     * @throws LogicException
     */
    public function isGreaterThan($valueToCompare, array $allFieldsData, array $fieldsToCompare): bool
    {
        if (empty($fieldsToCompare)) {
            throw new LogicException(
                "The greaterThan constraint is malformed. Expected: 'greaterThan:fieldToCompare'."
            );
        }

        foreach ($fieldsToCompare as $field) {
            if (!array_key_exists($field, $allFieldsData) || null === $allFieldsData[$field]) {
                continue;
            }

            if ($allFieldsData[$field] >= $valueToCompare) {
                return false;
            }
        }

        return true;
    }
}
