<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Constraint;

class NotRequiredIfOneExistConstraint implements ConstraintInterface
{
    public static function name(): string
    {
        return 'notRequiredIfOneExist';
    }

    public function validate($value, array $params = [], array $allData = []): bool
    {
        return $this->hasOneOfTheRequiredFieldsDefined($value, $allData, $params);
    }

    public function getMessage(string $valueName, $value, array $params = []): string
    {
        return sprintf(
            "%s is required because you didn't provide one of %s.",
            $valueName,
            implode(', ', $params)
        );
    }

    private function hasOneOfTheRequiredFieldsDefined($value, array $data, array $requiredFields): bool
    {
        if (null !== $value) {
            return !empty($requiredFields);
        }

        foreach ($requiredFields as $field) {
            if (isset($data[$field])) {
                return true;
            }
        }

        return false;
    }
}
