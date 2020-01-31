<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Constraint;

class StringConstraint implements ConstraintInterface
{
    public static function name(): string
    {
        return 'string';
    }

    /**
     * Params can contain a value "strict" which will validate the value against a strict string scalar comparison.
     */
    public function validate($value, array $params = [], array $allData = []): bool
    {
        $strictMode = isset($params[0]) && $params[0] == 'strict';

        return null === $value || $this->isString($value, $strictMode);
    }

    public function getMessage(string $valueName, $value, array $params = []): string
    {
        return sprintf('%s should be a string.', $valueName);
    }

    private function isString($value, bool $strict): bool
    {
        if ($strict) {
            return !is_numeric($value) && is_string($value);
        }

        return false !== filter_var($value);
    }
}
