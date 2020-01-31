<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Validator;

class ValidationResult
{
    /** @var array */
    private $validatedData;

    /** @var array */
    private $errors;

    public function __construct(array $validatedData, array $errors)
    {
        $this->validatedData = $validatedData;
        $this->errors = $errors;
    }

    public function isValid(): bool
    {
        return empty($this->errors);
    }

    public function getValidatedData(): array
    {
        return $this->validatedData;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
