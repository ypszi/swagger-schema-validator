<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Validator;

class ChainValidator implements ValidatorInterface
{
    /** @var ValidatorInterface[] */
    public $validators = [];

    public function add(ValidatorInterface $validator): self
    {
        $this->validators[] = $validator;

        return $this;
    }

    public function validate(array $data): ValidationResult
    {
        $validationResults = [];
        foreach ($this->validators as $validator) {
            $validationResults[] = $validator->validate($data);
        }

        return new ValidationResult(
            array_merge(
                ...
                array_map(
                    function (ValidationResult $validationResult) {
                        return $validationResult->getValidatedData();
                    },
                    $validationResults
                )
            ),
            array_merge(
                ...
                array_map(
                    function (ValidationResult $validationResult) {
                        return $validationResult->getErrors();
                    },
                    $validationResults
                )
            )
        );
    }
}
