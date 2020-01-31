<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Validator;

interface ValidatorInterface
{
    public function validate(array $data): ValidationResult;
}
