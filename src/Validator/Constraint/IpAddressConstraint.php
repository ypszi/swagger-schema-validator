<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Constraint;

class IpAddressConstraint implements ConstraintInterface
{
    public static function name(): string
    {
        return 'ipAddress';
    }

    public function validate($value, array $params = [], array $allData = []): bool
    {
        if (null === $value) {
            return true;
        }

        return false !== filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6);
    }

    public function getMessage(string $valueName, $value, array $params = []): string
    {
        return sprintf("'%s' should be a valid ip address.", $valueName);
    }
}
