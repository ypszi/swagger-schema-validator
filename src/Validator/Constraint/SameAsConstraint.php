<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Constraint;

use LogicException;

class SameAsConstraint implements ConstraintInterface
{
    public static function name(): string
    {
        return 'sameAs';
    }

    public function validate($value, array $params = [], array $allData = []): bool
    {
        if (null === $value) {
            return true;
        }

        return $this->isSame($value, $allData, $params);
    }

    public function getMessage(string $valueName, $value, array $params = []): string
    {
        return sprintf(
            'The field %s is not the same as %s.',
            $valueName,
            implode(', ', $params)
        );
    }

    /**
     * @throws LogicException
     */
    private function isSame($value, array $data, array $fieldsToCompare): bool
    {
        if (empty($fieldsToCompare)) {
            throw new LogicException("The sameAs constraint is malformed. Expected: 'sameAs:fieldToCompare'.");
        }

        foreach ($fieldsToCompare as $field) {
            if (!array_key_exists($field, $data) || $data[$field] !== $value) {
                return false;
            }
        }

        return true;
    }
}
