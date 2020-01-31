<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Constraint;

class MaxConstraint implements ConstraintInterface
{
    public static function name(): string
    {
        return 'max';
    }

    public function validate($value, array $params = [], array $allData = []): bool
    {
        if (empty($params)) {
            return true;
        }

        [$max] = $params;

        return null === $value || $value <= $max;
    }

    public function getMessage(string $valueName, $value, array $params = []): string
    {
        [$max] = $params;

        return sprintf('%s should be lower or equal to %s.', $valueName, $max);
    }
}
