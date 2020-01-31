<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Validator;

use RuntimeException;
use Throwable;

/**
 * @codeCoverageIgnore
 */
class ValidationException extends RuntimeException
{
    /** @var array */
    private $validationErrors;

    public function __construct($message = '', $code = 0, Throwable $previous = null, array $validationErrors = [])
    {
        parent::__construct($message, $code, $previous);

        $this->validationErrors = $validationErrors;
    }

    /**
     * @return array
     */
    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }
}
