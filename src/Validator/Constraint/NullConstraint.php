<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Constraint;

class NullConstraint implements ConstraintInterface
{
    public static function name(): string
    {
        return 'null';
    }

    public function validate($value, array $params = [], array $allData = []): bool
    {
        return null === $value;
    }

    public function getMessage(string $valueName, $value, array $params = []): string
    {
        return sprintf('%s should be null.', $valueName);
    }
}
