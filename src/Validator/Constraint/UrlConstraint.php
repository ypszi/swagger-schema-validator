<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Constraint;

class UrlConstraint implements ConstraintInterface
{
    public static function name(): string
    {
        return 'url';
    }

    public function validate($value, array $params = [], array $allData = []): bool
    {
        if (null === $value) {
            return true;
        }

        return false !== filter_var($value, FILTER_VALIDATE_URL);
    }

    public function getMessage(string $valueName, $value, array $params = []): string
    {
        return sprintf("%s must be a valid URL, '%s' provided.", $valueName, $value);
    }
}
