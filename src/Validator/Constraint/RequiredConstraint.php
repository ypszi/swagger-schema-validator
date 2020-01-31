<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Constraint;

class RequiredConstraint implements ConstraintInterface
{
    public static function name(): string
    {
        return 'required';
    }

    public function validate($value, array $params = [], array $allData = []): bool
    {
        return null !== $value && '' !== $value;
    }

    public function getMessage(string $valueName, $value, array $params = []): string
    {
        return sprintf('%s is required.', $valueName);
    }
}
