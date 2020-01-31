<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Constraint;

class BooleanConstraint implements ConstraintInterface
{
    public static function name(): string
    {
        return 'bool';
    }

    public function validate($value, array $params = [], array $allData = []): bool
    {
        return null === $value || in_array($value, [false, true, 0, 1, 'false', 'true', '0', '1'], true);
    }

    public function getMessage(string $valueName, $value, array $params = []): string
    {
        return sprintf('%s should be a boolean.', $valueName);
    }
}
