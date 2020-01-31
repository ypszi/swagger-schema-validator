<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Constraint;

use Ypszi\SwaggerSchemaValidator\Validator\Validator\Validator;

class RequiredIfExistConstraint implements ConstraintInterface
{
    public static function name(): string
    {
        return 'requiredIfExist';
    }

    public function validate($value, array $params = [], array $allData = []): bool
    {
        return null !== $value || $this->hasRequiredFieldsDefined($allData, $params);
    }

    public function getMessage(string $valueName, $value, array $params = []): string
    {
        return sprintf(
            "%s is required because you provided %s.",
            $valueName,
            implode(', ', array_map([$this, 'replaceWildcards'], $params))
        );
    }

    private function hasRequiredFieldsDefined(array $data, array $requiredFields): bool
    {
        if (empty($requiredFields)) {
            return true;
        }

        foreach ($requiredFields as $requiredField) {
            if (strpos($requiredField, Validator::ANY_KEY_WILDCARD) !== false) {
                $regexp = '/^' . $this->replaceWildcards($requiredField) . '(\..+)*$/';
                $foundFields = $this->pregGrep($regexp, $data);

                foreach ($foundFields as $foundField) {
                    if (isset($data[$foundField])) {
                        return false;
                    }
                }
            }
            elseif (isset($data[$requiredField])) {
                return false;
            }
        }

        return true;
    }

    private function replaceWildcards(string $string): string
    {
        return strtr($string, [Validator::ANY_KEY_WILDCARD => '0']);
    }

    private function pregGrep(string $regexp, array $data): array
    {
        $foundFields = [];

        foreach (array_keys($data) as $field) {
            if (preg_match($regexp, (string)$field, $matches) === 1) {
                $foundFields[] = array_shift($matches);
            }
        }

        return $foundFields;
    }
}
