<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Constraint;

use LogicException;

class MaxLengthConstraint implements ConstraintInterface
{
    public static function name(): string
    {
        return 'maxLength';
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

        $maxLength = current($params);

        return is_iterable($value) ? count($value) <= $maxLength : mb_strlen((string)$value) <= $maxLength;
    }

    public function getMessage(string $valueName, $value, array $params = []): string
    {
        return sprintf('%s length should be less than or equal to %s.', $valueName, current($params));
    }

    /**
     * @throws LogicException
     */
    private function validateParams(array $params): void
    {
        if (!isset($params[0])) {
            throw new LogicException(
                "The 'maxLength' constraint is malformed. Expected: 'maxLength:{digitalNumber}'."
            );
        }
    }
}
