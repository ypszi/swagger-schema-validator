<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Constraint;

class FalseConstraint implements ConstraintInterface
{
    public static function name(): string
    {
        return 'false';
    }

    public function validate($value, array $params = [], array $allData = []): bool
    {
        return null === $value || in_array($value, [false, 0, 'false', '0'], true);
    }

    public function getMessage(string $valueName, $value, array $params = []): string
    {
        return sprintf('%s should be false.', $valueName);
    }
}
