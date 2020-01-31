<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Constraint;

class EmailConstraint implements ConstraintInterface
{
    public static function name(): string
    {
        return 'email';
    }

    public function validate($value, array $params = [], array $allData = []): bool
    {
        if (null === $value) {
            return true;
        }

        if (!is_string($value)) {
            return false;
        }

        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function getMessage(string $valueName, $value, array $params = []): string
    {
        return sprintf('%s should be a valid email address.', $valueName);
    }
}
