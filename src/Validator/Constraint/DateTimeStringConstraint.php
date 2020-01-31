<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Constraint;

use DateTime;

class DateTimeStringConstraint implements ConstraintInterface
{
    public static function name(): string
    {
        return 'dateTimeString';
    }

    public function validate($value, array $params = [], array $allData = []): bool
    {
        if (null === $value) {
            return true;
        }

        if (!is_string($value)) {
            return false;
        }

        return false !== DateTime::createFromFormat($this->getFormatFromParams($params), $value);
    }

    public function getMessage(string $valueName, $value, array $params = []): string
    {
        return sprintf(
            '%s should be a valid datetime in "%s" format.',
            $valueName,
            $this->getFormatFromParams($params)
        );
    }

    private function getFormatFromParams(array $params): string
    {
        return $params[0] ?? DateTime::ATOM;
    }
}
