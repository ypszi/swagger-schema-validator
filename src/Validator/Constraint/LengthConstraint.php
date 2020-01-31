<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Constraint;

use LogicException;

class LengthConstraint implements ConstraintInterface
{
    public static function name(): string
    {
        return 'length';
    }

    public function validate($value, array $params = [], array $allData = []): bool
    {
        if (null === $value) {
            return true;
        }

        $this->validateParams($params);
        $this->validateValue($value);

        $length = current($params);

        return (is_string($value) && mb_strlen($value) == $length)
            || (is_array($value) && count($value) == $length);
    }

    public function getMessage(string $valueName, $value, array $params = []): string
    {
        return sprintf('%s length should be equal to %s.', $valueName, current($params));
    }

    /**
     * @throws LogicException
     */
    private function validateParams(array $params): void
    {
        if (!isset($params[0])) {
            throw new LogicException(
                "The 'length' constraint is malformed. Expected: 'length:{digitalNumber}'."
            );
        }
    }

    /**
     * @throws LogicException
     */
    private function validateValue($value): void
    {
        if (!is_string($value) && !is_array($value)) {
            throw new LogicException(
                "The 'length' constraint can be used to validate only strings or arrays length."
            );
        }
    }
}
