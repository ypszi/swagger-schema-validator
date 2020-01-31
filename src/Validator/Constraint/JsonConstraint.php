<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Constraint;

class JsonConstraint implements ConstraintInterface
{
    public static function name(): string
    {
        return 'json';
    }

    public function validate($value, array $params = [], array $allData = []): bool
    {
        if (null === $value) {
            return true;
        }

        @json_decode($value);

        return json_last_error() === JSON_ERROR_NONE;
    }

    public function getMessage(string $valueName, $value, array $params = []): string
    {
        return sprintf('%s should be a valid json string.', $valueName);
    }
}
