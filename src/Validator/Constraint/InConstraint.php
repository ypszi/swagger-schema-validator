<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Constraint;

class InConstraint implements ConstraintInterface
{
    public static function name(): string
    {
        return 'in';
    }

    public function validate($value, array $acceptedValues = [], array $allData = []): bool
    {
        return null === $value || in_array($value, $acceptedValues);
    }

    public function getMessage(string $valueName, $value, array $acceptedValues = []): string
    {
        return sprintf('%s should be one of (%s) values.', $valueName, implode(', ', $acceptedValues));
    }
}
