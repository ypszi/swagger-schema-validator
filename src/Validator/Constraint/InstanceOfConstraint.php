<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Constraint;

class InstanceOfConstraint implements ConstraintInterface
{
    public static function name(): string
    {
        return 'instanceOf';
    }

    public function validate($value, array $params = [], array $allData = []): bool
    {
        if (empty($params)) {
            return false;
        }

        [$instance] = $params;

        return null === $value || $value instanceof $instance;
    }

    public function getMessage(string $valueName, $value, array $params = []): string
    {
        [$instance] = $params;

        return sprintf('%s should be an instance of %s.', $valueName, $instance);
    }
}
