<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Constraint;

class ArrayConstraint implements ConstraintInterface
{
    public static function name(): string
    {
        return 'array';
    }

    public function validate($value, array $params = [], array $allData = []): bool
    {
        if (null === $value) {
            return true;
        }

        return is_array($value);
    }

    public function getMessage(string $valueName, $value, array $params = []): string
    {
        return sprintf('%s should be an array.', $valueName);
    }
}
