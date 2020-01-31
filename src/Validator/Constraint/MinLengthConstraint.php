<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Constraint;

use LogicException;

class MinLengthConstraint implements ConstraintInterface
{
    public static function name(): string
    {
        return 'minLength';
    }

    /**
     * @throws LogicException
     */
    public function validate($value, array $params = [], array $allData = []): bool
    {
        if (null === $value) {
            return true;
        }
        elseif (is_object($value)) {
            return false;
        }

        $this->validateParams($params);

        $minLength = current($params);

        return is_iterable($value) ? count($value) >= $minLength : mb_strlen((string)$value) >= $minLength;
    }

    public function getMessage(string $valueName, $value, array $params = []): string
    {
        return sprintf('%s length should be greater than or equal to %s.', $valueName, current($params));
    }

    /**
     * @throws LogicException
     */
    private function validateParams(array $params): void
    {
        if (!isset($params[0])) {
            throw new LogicException(
                "The 'minLength' constraint is malformed. Expected: 'minLength:{digitalNumber}'."
            );
        }
    }
}
