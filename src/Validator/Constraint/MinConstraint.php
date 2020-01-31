<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Constraint;

class MinConstraint implements ConstraintInterface
{
    public static function name(): string
    {
        return 'min';
    }

    public function validate($value, array $params = [], array $allData = []): bool
    {
        if (empty($params)) {
            return true;
        }

        [$min] = $params;

        return null === $value || $value >= $min;
    }

    public function getMessage(string $valueName, $value, array $params = []): string
    {
        [$min] = $params;

        return sprintf('%s should be greater or equal to %s.', $valueName, $min);
    }
}
