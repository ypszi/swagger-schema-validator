<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Constraint;

class FloatConstraint implements ConstraintInterface
{
    public static function name(): string
    {
        return 'float';
    }

    public function validate($value, array $params = [], array $allData = []): bool
    {
        if ($value === true) {
            return false;
        }

        return null === $value || false !== filter_var($value, FILTER_VALIDATE_FLOAT);
    }

    public function getMessage(string $valueName, $value, array $params = []): string
    {
        return sprintf('%s should be a float.', $valueName);
    }
}
