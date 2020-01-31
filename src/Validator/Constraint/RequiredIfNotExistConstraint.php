<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Constraint;

class RequiredIfNotExistConstraint implements ConstraintInterface
{
    public static function name(): string
    {
        return 'requiredIfNotExist';
    }

    public function validate($value, array $params = [], array $allData = []): bool
    {
        return null !== $value || $this->hasRequiredFieldsDefined($allData, $params);
    }

    public function getMessage(string $valueName, $value, array $params = []): string
    {
        return sprintf("%s is required because you didn't provide %s.", $valueName, implode(', ', $params));
    }

    private function hasRequiredFieldsDefined($data, array $requiredFields): bool
    {
        if (empty($requiredFields)) {
            return false;
        }

        foreach ($requiredFields as $field) {
            $fieldKeys = [$field];
            if (strpos($field, '.') !== false) {
                $fieldKeys = explode('.', $field);
            }
            foreach ($fieldKeys as $fieldKey) {
                if (!isset($data[$fieldKey])) {
                    return false;
                }

                array_shift($fieldKeys);
                if (!empty($fieldKeys)) {
                    return $this->hasRequiredFieldsDefined($data[$fieldKey], [implode('.', $fieldKeys)]);
                }
            }
        }

        return true;
    }
}
