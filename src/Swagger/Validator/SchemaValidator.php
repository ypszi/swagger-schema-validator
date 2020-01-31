<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Swagger\Validator;

use cebe\openapi\Reader as OpenApiParser;
use cebe\openapi\spec\PathItem;
use cebe\openapi\spec\Paths;
use cebe\openapi\spec\Responses;
use cebe\openapi\spec\Schema;
use cebe\openapi\SpecBaseObject;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\ArrayConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\Base64Constraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\BooleanConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\ConstraintCollection;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\DateTimeStringConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\FloatConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\IntegerConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\RegexpConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\StringConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Rule\RuleNormalizer;
use Ypszi\SwaggerSchemaValidator\Validator\Validator\ValidationResult;
use Ypszi\SwaggerSchemaValidator\Validator\Validator\Validator;

class SchemaValidator
{
    /** @var ConstraintCollection */
    private $constraintCollection;

    /** @var RuleNormalizer */
    private $ruleNormalizer;

    public function __construct(ConstraintCollection $constraintCollection, RuleNormalizer $ruleNormalizer)
    {
        $this->constraintCollection = $constraintCollection;
        $this->ruleNormalizer = $ruleNormalizer;
    }

    public function validateSwaggerSchema(
        string $swaggerFilePath,
        ResponseInterface $response,
        string $uri,
        string $method,
        int $statusCode = 200
    ): ValidationResult {
        $swaggerSchema = OpenApiParser::readFromYamlFile(realpath($swaggerFilePath));
        $responseSchema = $this->findSwaggerResponseSchema($swaggerSchema, $uri, $method, $statusCode);

        return $this->validateSchema($responseSchema, $response);
    }

    private function findSwaggerResponseSchema(
        SpecBaseObject $swaggerSchema,
        string $uri,
        string $method,
        int $statusCode = 200
    ): Schema {
        $method = strtolower($method);
        $swaggerPath = $this->findSwaggerPath($swaggerSchema, $uri, $method);
        $responses = $swaggerPath->{$method}->responses ?? new Responses([]);

        if (!$responses->hasResponse($statusCode)) {
            throw new InvalidArgumentException(
                sprintf('Swagger response schema not found for statusCode: %s', $statusCode)
            );
        }

        return $responses->getResponse($statusCode)->content['application/json']->schema;
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
        array $expectedResponse = []
    ): ValidationResult {
        if (isset($responseSchema->type)) {
            $schemaDataType = $responseSchema->type;

            if ($this->isPrimitiveType($schemaDataType)) {
                $rulesForFields = $this->createRulesForFields($responseSchema, $schemaPrefix);

                return $this->validateSubSchema($rulesForFields, $response);
            }

            if ($schemaDataType === 'object') {
                $validationResult = new ValidationResult([], []);

                foreach ($responseSchema->properties as $propertyKey => $property) {
                    $propertyValidationResult = $this->validateSchema(
                        $property,
                        $response,
                        $schemaPrefix . $propertyKey . '.',
                        $expectedResponse
                    );

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
                $rulesForFields = $this->createRulesForFields($responseSchema->items, '*');

                return $this->validateSubSchema($rulesForFields, $response);
            }
        }

        if (isset($responseSchema->allOf) && count($responseSchema->allOf)) {
            $rulesForFields = [];

            foreach ($responseSchema->allOf as $schema) {
                $rulesForFields = array_merge(
                    $rulesForFields,
                    $this->createRulesForFields($schema, substr($schemaPrefix, 0, -1))
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

        $validator = new Validator($this->constraintCollection, $this->ruleNormalizer, $rules);

        return $validator->validate($responseBody);
    }

    private function createRulesForFields(Schema $schema, string $schemaPrefix = '', array $fields = []): array
    {
        if (!isset($schema->type)) {
            throw new InvalidArgumentException('Schema does not have "type" property');
        }

        $schemaDataType = $schema->type;

        if ($this->isPrimitiveType($schemaDataType)) {
            $isNullable = isset($schema->nullable) && $schema->nullable === true;
            $fields[$schemaPrefix] = $this->getRules($schema, $isNullable, $schemaPrefix);

            return $fields;
        }

        if ($schemaDataType === 'object') {
            foreach ($schema->properties as $propertyKey => $property) {
                $prefixedPropertyKey = $schemaPrefix . '.' . $propertyKey;
                $isNullable = isset($property->nullable) && $property->nullable === true;
                $dataType = $property->type;

                if ($this->isPrimitiveType($dataType)) {
                    $fields[$prefixedPropertyKey] = $this->getRules($property, $isNullable, $schemaPrefix);
                }
                else {
                    $fields = array_merge(
                        $fields,
                        $this->createRulesForFields($property, $prefixedPropertyKey, $fields)
                    );
                }
            }
        }

        if ($schemaDataType === 'array') {
            $isNullable = isset($schema->nullable) && $schema->nullable === true;
            $items = $schema->items;
            $dataType = $items->type;

            if ($this->isPrimitiveType($dataType)) {
                $fields[$schemaPrefix] = $this->getRules($schema, $isNullable);
            }
            else {
                $fields[$schemaPrefix] = $this->getRules($schema, $isNullable);
                $fields = array_merge(
                    $fields,
                    $this->createRulesForFields($items, $schemaPrefix . '.*', $fields)
                );
            }
        }

        return $fields;
    }

    private function isPrimitiveType(string $type): bool
    {
        return in_array($type, ['string', 'number', 'integer', 'boolean'], true);
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
    private function getRules(Schema $schema, bool $isNullable = false, string $fieldPrefix = ''): array
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
                    $rules[] = $this->getRuleByFormat($schema->format);
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

    private function getRuleByFormat(string $format): string
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
