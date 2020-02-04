<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Test\Integration\Swagger\Validator;


use DateTime;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Ypszi\SwaggerSchemaValidator\Swagger\Validator\SchemaValidator;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\ArrayConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\ArrayOfConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\Base64Constraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\BooleanConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\ConstraintCollection;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\DateTimeStringConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\EmailConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\FalseConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\FloatConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\GreaterThanConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\GreaterThanOrEqualToConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\InConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\InstanceOfConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\IntegerConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\IpAddressConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\JsonConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\LengthConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\LessThanConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\LessThanOrEqualToConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\MaxConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\MaxLengthConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\MinConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\MinLengthConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\NotRequiredIfOneExistConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\NullConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\RegexpConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\RequiredConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\RequiredIfExistConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\RequiredIfNotExistConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\SameAsConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\StringConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\TrueConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\UrlConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Rule\RuleNormalizer;

class SchemaValidatorTest extends TestCase
{
    /** @var SchemaValidator */
    private $subject;

    protected function setUp(): void
    {
        $constraintCollection = new ConstraintCollection();
        $constraintCollection
            ->add(new BooleanConstraint())
            ->add(new FloatConstraint())
            ->add(new InConstraint())
            ->add(new InstanceofConstraint())
            ->add(new IntegerConstraint())
            ->add(new MaxConstraint())
            ->add(new MinConstraint())
            ->add(new NotRequiredIfOneExistConstraint())
            ->add(new RequiredConstraint())
            ->add(new RequiredIfExistConstraint())
            ->add(new RequiredIfNotExistConstraint())
            ->add(new StringConstraint())
            ->add(new EmailConstraint())
            ->add(new NullConstraint())
            ->add(new DateTimeStringConstraint())
            ->add(new FalseConstraint())
            ->add(new TrueConstraint())
            ->add(new SameAsConstraint())
            ->add(new GreaterThanConstraint())
            ->add(new GreaterThanOrEqualToConstraint())
            ->add(new LessThanConstraint())
            ->add(new LessThanOrEqualToConstraint())
            ->add(new RegexpConstraint())
            ->add(new LengthConstraint())
            ->add(new MinLengthConstraint())
            ->add(new MaxLengthConstraint())
            ->add(new IpAddressConstraint())
            ->add(new UrlConstraint())
            ->add(new JsonConstraint())
            ->add(new ArrayConstraint())
            ->add(new Base64Constraint());

        $arrayOf = new ArrayOfConstraint($constraintCollection);

        $constraintCollection->add($arrayOf);

        $this->subject = new SchemaValidator($constraintCollection, new RuleNormalizer());
    }

    /**
     * @param ResponseInterface $response
     * @param string $uri
     * @param string $method
     * @param int $statusCode
     *
     * @dataProvider responseProvider
     */
    public function testValidateSwaggerSchema(
        ResponseInterface $response,
        string $uri,
        string $method,
        int $statusCode
    ): void {
        $validationResult = $this->subject->validateSwaggerSchema(
            __DIR__ . '/../../data/swagger-testing.yaml',
            $response,
            $uri,
            $method,
            $statusCode
        );

        $this->assertEmpty($validationResult->getErrors());
    }

    public function responseProvider(): array
    {
        return [
            [
                new Response(204),
                '/some/testing/url',
                'GET',
                204,
            ],
            [
                new Response(
                    200,
                    [],
                    json_encode(
                        [
                            'id' => 1,
                            'firstName' => 'foo',
                            'lastName' => 'bar',
                            'email' => 'some@example.com',
                            'username' => 'my_username',
                            'createdAt' => (new DateTime())->format(DATE_ATOM),
                        ]
                    )
                ),
                '/some/testing/url/{param}',
                'GET',
                200,
            ],
        ];
    }

    /**
     * @param ResponseInterface $response
     * @param string $uri
     * @param string $method
     * @param int $statusCode
     * @param array $expectedErrors
     *
     * @dataProvider invalidResponseProvider
     */
    public function testValidateSwaggerSchemaFails(
        ResponseInterface $response,
        string $uri,
        string $method,
        int $statusCode,
        array $expectedErrors
    ): void {
        $validationResult = $this->subject->validateSwaggerSchema(
            __DIR__ . '/../../data/swagger-testing.yaml',
            $response,
            $uri,
            $method,
            $statusCode
        );

        $this->assertEquals($expectedErrors, $validationResult->getErrors());
    }

    public function invalidResponseProvider(): array
    {
        return [
            [
                'response' => new Response(200),
                'uri' => '/some/testing/url',
                'method' => 'GET',
                'statusCode' => 204,
                'expectedErrors' => [
                    'statusCode' => ['statusCode should be one of (204) values.'],
                ],
            ],
            [
                'response' => new Response(200, [], json_encode([])),
                'uri' => '/some/testing/url/{param}',
                'method' => 'GET',
                'statusCode' => 200,
                'expectedErrors' => [
                    'id' => ['id is required.'],
                    'firstName' => ['firstName is required.'],
                    'lastName' => ['lastName is required.'],
                    'email' => ['email is required.'],
                    'createdAt' => ['createdAt is required.'],
                ],
            ],
        ];
    }
}
