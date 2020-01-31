<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Validator;

trait ValidateDataTrait
{
    /**
     * @param ValidatorInterface $validator
     * @param array $data
     *
     * @return array
     */
    protected function getValidatedData(ValidatorInterface $validator, array $data): array
    {
        $result = $validator->validate($data);

        if (!$result->isValid()) {
            throw new ValidationException('Validation error', 400, null, $result->getErrors());
        }

        return $result->getValidatedData();
    }
}
