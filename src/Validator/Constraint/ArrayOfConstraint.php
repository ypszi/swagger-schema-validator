<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Constraint;

class ArrayOfConstraint implements ConstraintInterface
{
    /** @var ConstraintCollection */
    private $constraintCollection;

    public function __construct(ConstraintCollection $constraintCollection)
    {
        $this->constraintCollection = $constraintCollection;
    }

    public static function name(): string
    {
        return 'arrayOf';
    }

    public function validate($value, array $params = [], array $allData = []): bool
    {
        if (empty($params)) {
            return false;
        }

        [$constraintName] = $params;

        return null === $value || (is_array($value) && $this->validateValues($value, $constraintName));
    }

    public function getMessage(string $valueName, $value, array $params = []): string
    {
        [$constraintName] = $params;

        return sprintf('%s should be an array of %s.', $valueName, $constraintName);
    }

    private function validateValues(array $values, string $constraintName): bool
    {
        $constraint = $this->constraintCollection->get($constraintName);

        foreach ($values as $value) {
            if (!$constraint->validate($value)) {
                return false;
            }
        }

        return true;
    }
}
