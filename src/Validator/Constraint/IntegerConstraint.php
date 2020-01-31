<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Constraint;

class IntegerConstraint implements ConstraintInterface
{
    public static function name(): string
    {
        return 'int';
    }

    public function validate($value, array $params = [], array $allData = []): bool
    {
        if ($value === true) {
            return false;
        }

        return null === $value || false !== filter_var($value, FILTER_VALIDATE_INT);
    }

    public function getMessage(string $valueName, $value, array $params = []): string
    {
        return sprintf('%s should be an integer.', $valueName);
    }
}
