<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Constraint;

class RegexpConstraint implements ConstraintInterface
{
    public static function name(): string
    {
        return 'regexp';
    }

    public function validate($value, array $params = [], array $allData = []): bool
    {
        if (null === $value) {
            return true;
        }

        [$regexp] = $params;

        return false !== filter_var(
                $value,
                FILTER_VALIDATE_REGEXP,
                ['options' => ['regexp' => $regexp]]
            );
    }

    public function getMessage(string $valueName, $value, array $params = []): string
    {
        [$regexp] = $params;

        return sprintf("%s should match regular expression: %s", $valueName, $regexp);
    }
}
