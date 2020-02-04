<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Swagger\Validator;

use cebe\openapi\exceptions\TypeErrorException;
use cebe\openapi\Reader as OpenApiParser;
use cebe\openapi\spec\PathItem;
use cebe\openapi\spec\Paths;
use cebe\openapi\spec\Responses;
use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type;
use cebe\openapi\SpecBaseObject;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\ArrayConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\Base64Constraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\BooleanConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\DateTimeStringConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\FloatConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\IntegerConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\RegexpConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\StringConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Validator\ValidationResult;
use Ypszi\SwaggerSchemaValidator\Validator\Validator\Validator;

class SchemaValidator extends Validator
{
    public function validateSwaggerSchema(
        string $swaggerFilePath,
        ResponseInterface $response,
        string $uri,
        string $method,
        int $statusCode = 200
    ): ValidationResult {
        $swaggerSchema = OpenApiParser::readFromYamlFile(realpath($swaggerFilePath));
        $responses = $this->findSwaggerResponseSchema($swaggerSchema, $uri, $method, $statusCode);

        $responseSchema = $responses->getResponse($statusCode);

        if (!isset($responseSchema->content['application/json']->schema)) {
            $this->setRules(['statusCode' => 'in:' . implode(',', array_keys($responses->getResponses()))]);

            return $this->validate(['statusCode' => $response->getStatusCode()]);
        }

        return $this->validateSchema($responseSchema->content['application/json']->schema, $response);
    }

    /**
     * @param SpecBaseObject $swaggerSchema
     * @param string $uri
     * @param string $method
     * @param int $statusCode
     *
     * @return Responses
     *
     * @throws InvalidArgumentException when response schema not found
     * @throws TypeErrorException
     */
    private function findSwaggerResponseSchema(
        SpecBaseObject $swaggerSchema,
        string $uri,
        string $method,
        int $statusCode = 200
    ): Responses {
        $method = strtolower($method);
        $swaggerPath = $this->findSwaggerPath($swaggerSchema, $uri, $method);
        $responses = $swaggerPath->{$method}->responses ?? new Responses([]);

        if (!$responses->hasResponse($statusCode)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Swagger response schema not found for statusCode, method, uri: %s - [%s] %s',
                    $statusCode,
                    $method,
                    $uri
                )
            );
        }

        return $responses;
    }

    private function findSwaggerPath(SpecBaseObject $swaggerSchema, string $uri, string $method): PathItem
    {
        $swaggerPaths = $swaggerSchema->paths ?? new Paths([]);
        $regexp = '/^' . strtr($uri, ['{' => '\{', '}' => '\}', '/' => '\/']) . '$/';
        $method = strtolower($method);

        foreach ($swaggerPaths as $swaggerPath => $path) {
            if (preg_match($regexp, $swaggerPath) === 1) {
                return $path;
            }
        }

        throw new InvalidArgumentException(
            sprintf('Swagger schema not found for path: [%s] %s', $method, $uri)
        );
    }

    private function validateSchema(
        Schema $responseSchema,
        ResponseInterface $response,
        string $schemaPrefix = '',
        string $fieldKey = null
    ): ValidationResult {
        if (isset($responseSchema->type)) {
            $schemaDataType = $responseSchema->type;

            if (Type::isScalar($schemaDataType)) {
                $rulesForFields = $this->createRulesForFields($responseSchema, $schemaPrefix, $fieldKey);

                return $this->validateSubSchema($rulesForFields, $response);
            }

            if ($schemaDataType === 'object') {
                $validationResult = new ValidationResult([], []);

                foreach ($responseSchema->properties as $propertyKey => $property) {
                    if (isset($property->type) && Type::isScalar($property->type)) {
                        $propertyValidationResult = $this->validateSchema(
                            $property,
                            $response,
                            $schemaPrefix . $propertyKey,
                            $propertyKey
                        );
                    }
                    else {
                        $propertyValidationResult = $this->validateSchema(
                            $property,
                            $response,
                            $schemaPrefix . $propertyKey . '.',
                            $propertyKey
                        );
                    }

                    $validationResult = new ValidationResult(
                        array_merge(
                            $validationResult->getValidatedData(),
                            $propertyValidationResult->getValidatedData()
                        ),
                        array_merge($validationResult->getErrors(), $propertyValidationResult->getErrors())
                    );
                }

                return $validationResult;
            }

            if ($schemaDataType === 'array') {
                $rulesForFields = $this->createRulesForFields($responseSchema->items, '*.');

                return $this->validateSubSchema($rulesForFields, $response);
            }
        }

        if (isset($responseSchema->allOf) && count($responseSchema->allOf)) {
            $rulesForFields = [];

            foreach ($responseSchema->allOf as $schema) {
                $rulesForFields = array_merge(
                    $rulesForFields,
                    $this->createRulesForFields($schema, $schemaPrefix, $fieldKey)
                );
            }

            return $this->validateSubSchema($rulesForFields, $response);
        }

        // TODO deal with oneOf, anyOf, not

//		if (isset($responseSchema->oneOf) && count($responseSchema->oneOf))
//		{
//		}
//
//		if (isset($responseSchema->anyOf) && count($responseSchema->anyOf))
//		{
//		}
//
//		if (isset($responseSchema->not))
//		{
//		}

        throw new RuntimeException('Invalid swagger schema found.');
    }

    private function validateSubSchema(array $rules, ResponseInterface $response): ValidationResult
    {
        $responseBody = json_decode((string)$response->getBody(), true);

        $response->getBody()->rewind();

        $this->setRules($rules);

        return $this->validate($responseBody);
    }

    private function createRulesForFields(
        Schema $schema,
        string $schemaPrefix = '',
        string $fieldKey = null,
        array $fields = []
    ): array {
        if (!isset($schema->type)) {
            throw new InvalidArgumentException('Schema does not have "type" property');
        }

        $schemaDataType = $schema->type;

        if (Type::isScalar($schemaDataType)) {
            $isNullable = isset($schema->nullable) && $schema->nullable === true;

            if ($schemaPrefix === (string)$fieldKey) {
                $fields[$fieldKey] = $this->createSchemaRules($schema, $isNullable);
            }
            else {
                $fields[$schemaPrefix . $fieldKey] = $this->createSchemaRules($schema, $isNullable, $schemaPrefix);
            }

            return $fields;
        }

        if ($schemaDataType === 'object') {
            foreach ($schema->properties as $propertyKey => $property) {
                $isNullable = isset($property->nullable) && $property->nullable === true;

                if (isset($property->type) && Type::isScalar($property->type)) {
                    $prefixedPropertyKey = $schemaPrefix . $propertyKey;

                    $fields[$prefixedPropertyKey] = $this->createSchemaRules(
                        $property,
                        $isNullable,
                        $this->removeLeafField($schemaPrefix)
                    );
                }
                else {
                    $prefixedPropertyKey = $schemaPrefix . $propertyKey . '.';

                    $fields = array_merge(
                        $fields,
                        $this->createRulesForFields($property, $prefixedPropertyKey, $fieldKey, $fields)
                    );
                }
            }
        }

        if ($schemaDataType === 'array') {
            $isNullable = isset($schema->nullable) && $schema->nullable === true;
            $items = $schema->items;
            $dataType = $items->type;
            $field = $this->removeLeafField($schemaPrefix);

            if (Type::isScalar($dataType)) {
                $fields[$field] = $this->createSchemaRules($schema, $isNullable, $this->removeLeafField($field));
            }
            else {
                $fields[$field] = $this->createSchemaRules($schema, $isNullable, $this->removeLeafField($field));
                $fields = array_merge(
                    $fields,
                    $this->createRulesForFields($items, $schemaPrefix . '*.', $fieldKey, $fields)
                );
            }
        }

        return $fields;
    }

    /**
     * @param Schema $schema
     * @param bool $isNullable [optional] <b>FALSE</b> by default
     * @param string $fieldPrefix
     *
     * @return array
     *
     * @see  https://swagger.io/docs/specification/data-models/data-types/
     */
    private function createSchemaRules(Schema $schema, bool $isNullable = false, string $fieldPrefix = ''): array
    {
        $rules = [];

        if ($isNullable === false) {
            if (empty($fieldPrefix)) {
                $rules[] = 'required';
            }
            else {
                $rules[] = 'requiredIfExist:' . $fieldPrefix;
            }
        }

        $dataType = $schema->type;

        switch ($dataType) {
            case 'string':
                if (isset($schema->format)) {
                    $rules[] = $this->createSchemaRuleByFormat($schema->format);
                }
                elseif (isset($schema->pattern)) {
                    $rules[] = RegexpConstraint::name() . ':' . $schema->pattern;
                }
                else {
                    $rules[] = StringConstraint::name();
                }

                break;
            case 'number':
                $rules[] = FloatConstraint::name();

                break;
            case 'integer':
                $rules[] = IntegerConstraint::name();

                break;
            case 'boolean':
                $rules[] = BooleanConstraint::name();

                break;
            case 'array':
                $rules[] = ArrayConstraint::name();

                break;
            case 'object':
                break;
        }

        return $rules;
    }

    private function createSchemaRuleByFormat(string $format): string
    {
        switch ($format) {
            case 'date':
                $rule = DateTimeStringConstraint::name() . ':Y-m-d';

                break;
            case 'date-time':
                $rule = DateTimeStringConstraint::name() . ':' . DATE_ATOM;

                break;
            case 'byte':
                $rule = Base64Constraint::name();

                break;
            default:
                $rule = StringConstraint::name();

                break;
        }

        return $rule;
    }
}
