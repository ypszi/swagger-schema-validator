<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Constraint;

class TrueConstraint implements ConstraintInterface
{
    public static function name(): string
    {
        return 'true';
    }

    public function validate($value, array $params = [], array $allData = []): bool
    {
        return null === $value || in_array($value, [true, 1, 'true', '1'], true);
    }

    public function getMessage(string $valueName, $value, array $params = []): string
    {
        return sprintf('%s should be true.', $valueName);
    }
}
