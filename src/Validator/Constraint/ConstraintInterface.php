<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Constraint;

interface ConstraintInterface
{
    public static function name(): string;

    public function validate($value, array $params = [], array $allData = []): bool;

    public function getMessage(string $valueName, $value, array $params = []): string;
}
