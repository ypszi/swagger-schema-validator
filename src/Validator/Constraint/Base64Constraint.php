<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Constraint;

class Base64Constraint implements ConstraintInterface
{
    public static function name(): string
    {
        return 'base64';
    }

    public function validate($value, array $params = [], array $allData = []): bool
    {
        return null === $value || base64_decode($value, true) !== false;
    }

    public function getMessage(string $valueName, $value, array $params = []): string
    {
        return sprintf(
            '%s is not base64 encoded properly.',
            $valueName
        );
    }
}
