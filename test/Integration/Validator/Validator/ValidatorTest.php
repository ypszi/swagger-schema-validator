<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Test\Integration\Validator\Validator;

use DateTime;
use InvalidArgumentException;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use stdClass;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\ArrayConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\ArrayOfConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\Base64Constraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\BooleanConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\ConstraintCollection;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\ConstraintInterface;
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
use Ypszi\SwaggerSchemaValidator\Validator\Validator\ValidationResult;
use Ypszi\SwaggerSchemaValidator\Validator\Validator\Validator;

class ValidatorTest extends TestCase
{
    public function testItSucceedWhenValidatingDataMatchingTheRules(): void
    {
        $validator = $this->createTestValidator(
            [
                'test1' => 'required',
                'test2' => 'required|int',
                'test3' => 'string',
                'test4' => 'string',
                'test5' => 'int|requiredIfNotExist:test3,test4',
                'test6' => 'arrayOf:bool',
                'test7' => 'max:3',
                'password' => 'required|string',
                'passwordConfirmation' => 'required|string|sameAs:password',
                'test8' => 'greaterThan:test2',
                'test9' => 'int',
                'test10' => 'int|greaterThanOrEqualTo:test2',
                'test11' => 'int|lessThanOrEqualTo:test2',
                'test12' => 'int|lessThan:test2',
                'test13' => 'array',
                'dateFrom' => 'dateTimeString',
                'dateTo' => 'dateTimeString|greaterThan:dateFrom',
                'date' => 'greaterThan:dateFrom',
            ]
        );

        $data = [
            'test1' => 'data',
            'test2' => 5,
            'test5' => 3,
            'password' => 'test',
            'passwordTest' => 'test',
            'passwordConfirmation' => 'test',
            'test8' => '6',
            'test9' => 5,
            'test10' => 5,
            'test11' => 5,
            'test12' => 4,
            'dateFrom' => '2005-08-14T15:52:01+00:00',
            'dateTo' => '2005-09-15T15:52:01+00:00',
            'date' => '2005-08-16 05:52:01',
        ];

        $result = $validator->validate($data);

        $this->assertTrue($result->isValid());
        $this->assertEmpty($result->getErrors());
        $this->assertEquals(
            [
                'test1' => 'data',
                'test2' => 5,
                'test3' => null,
                'test4' => null,
                'test5' => 3,
                'test6' => null,
                'test7' => null,
                'password' => 'test',
                'passwordConfirmation' => 'test',
                'test8' => '6',
                'test9' => 5,
                'test10' => 5,
                'test11' => 5,
                'test12' => 4,
                'test13' => null,
                'dateFrom' => '2005-08-14T15:52:01+00:00',
                'dateTo' => '2005-09-15T15:52:01+00:00',
                'date' => '2005-08-16 05:52:01',
            ],
            $result->getValidatedData()
        );
    }

    /**
     * @param array $data
     * @param array $expectedValidationData
     * @param array $expectedErrors
     *
     * @dataProvider cropsValidationDataProvider
     */
    public function testCropsValidation(array $data, array $expectedValidationData, array $expectedErrors): void
    {
        $validator = $this->createTestValidator(
            [
                'data.crops' => 'required|array',
                'data.crops.*' => 'required|array',
                'data.crops.*.name' => 'required|string|in:' . implode(
                        ',',
                        ['16_9', '4_3', 'portrait']
                    ),
                'data.crops.*.source' => 'required|string|in:' . implode(',', ['16_9', 'original']),
                'data.crops.*.area.topLeft.unit' => 'required|string',
                'data.crops.*.area.topLeft.x' => 'required|float',
                'data.crops.*.area.topLeft.y' => 'required|float',
                'data.crops.*.area.bottomRight.unit' => 'required|string',
                'data.crops.*.area.bottomRight.x' => 'required|float',
                'data.crops.*.area.bottomRight.y' => 'required|float',
            ]
        );

        $result = $validator->validate($data);

        $this->assertEquals(
            $expectedErrors,
            $result->getErrors(),
            'Errors do not match expected.'
        );

        $this->assertEquals(
            $expectedValidationData,
            $result->getValidatedData(),
            'Validation data do not match expected.'
        );
    }

    public function cropsValidationDataProvider(): array
    {
        return [
            'crops not array' => [
                'data' => [
                    'data' => [
                        'crops' => 'not array',
                    ],
                ],
                'expectedValidationData' => [
                    'data' => [
                        'crops' => [
                            [
                                'name' => null,
                                'source' => null,
                                'area' => [
                                    'topLeft' => [
                                        'unit' => null,
                                        'x' => null,
                                        'y' => null,
                                    ],
                                    'bottomRight' => [
                                        'unit' => null,
                                        'x' => null,
                                        'y' => null,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedErrors' => [
                    'data.crops' => ['data.crops should be an array.'],
                    'data.crops.0' => ['data.crops.0 is required.'],
                    'data.crops.0.name' => ['data.crops.0.name is required.'],
                    'data.crops.0.source' => ['data.crops.0.source is required.'],
                    'data.crops.0.area.topLeft.unit' => ['data.crops.0.area.topLeft.unit is required.'],
                    'data.crops.0.area.topLeft.x' => ['data.crops.0.area.topLeft.x is required.'],
                    'data.crops.0.area.topLeft.y' => ['data.crops.0.area.topLeft.y is required.'],
                    'data.crops.0.area.bottomRight.unit' => ['data.crops.0.area.bottomRight.unit is required.'],
                    'data.crops.0.area.bottomRight.x' => ['data.crops.0.area.bottomRight.x is required.'],
                    'data.crops.0.area.bottomRight.y' => ['data.crops.0.area.bottomRight.y is required.'],
                ],
            ],

            'crops not contains arrays' => [
                'data' => [
                    'data' => [
                        'crops' => [
                            'not array',
                        ],
                    ],
                ],
                'expectedValidationData' => [
                    'data' => [
                        'crops' => [
                            [
                                'name' => null,
                                'source' => null,
                                'area' => [
                                    'topLeft' => [
                                        'unit' => null,
                                        'x' => null,
                                        'y' => null,
                                    ],
                                    'bottomRight' => [
                                        'unit' => null,
                                        'x' => null,
                                        'y' => null,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedErrors' => [
                    'data.crops.0' => ['data.crops.0 should be an array.'],
                    'data.crops.0.name' => ['data.crops.0.name is required.'],
                    'data.crops.0.source' => ['data.crops.0.source is required.'],
                    'data.crops.0.area.topLeft.unit' => ['data.crops.0.area.topLeft.unit is required.'],
                    'data.crops.0.area.topLeft.x' => ['data.crops.0.area.topLeft.x is required.'],
                    'data.crops.0.area.topLeft.y' => ['data.crops.0.area.topLeft.y is required.'],
                    'data.crops.0.area.bottomRight.unit' => ['data.crops.0.area.bottomRight.unit is required.'],
                    'data.crops.0.area.bottomRight.x' => ['data.crops.0.area.bottomRight.x is required.'],
                    'data.crops.0.area.bottomRight.y' => ['data.crops.0.area.bottomRight.y is required.'],
                ],
            ],

            'missing crop name' => [
                'data' => [
                    'data' => [
                        'crops' => [
                            [
                                'source' => 'original',
                                'area' => [
                                    'topLeft' => [
                                        'unit' => 'some unit',
                                        'x' => 1,
                                        'y' => 1,
                                    ],
                                    'bottomRight' => [
                                        'unit' => 'some unit',
                                        'x' => 10,
                                        'y' => 10,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedValidationData' => [
                    'data' => [
                        'crops' => [
                            [
                                'name' => null,
                                'source' => 'original',
                                'area' => [
                                    'topLeft' => [
                                        'unit' => 'some unit',
                                        'x' => 1,
                                        'y' => 1,
                                    ],
                                    'bottomRight' => [
                                        'unit' => 'some unit',
                                        'x' => 10,
                                        'y' => 10,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedErrors' => [
                    'data.crops.0.name' => ['data.crops.0.name is required.'],
                ],
            ],

            'invalid crop name' => [
                'data' => [
                    'data' => [
                        'crops' => [
                            [
                                'name' => 'invalid',
                                'source' => 'original',
                                'area' => [
                                    'topLeft' => [
                                        'unit' => 'some unit',
                                        'x' => 1,
                                        'y' => 1,
                                    ],
                                    'bottomRight' => [
                                        'unit' => 'some unit',
                                        'x' => 10,
                                        'y' => 10,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedValidationData' => [
                    'data' => [
                        'crops' => [
                            [
                                'name' => null,
                                'source' => 'original',
                                'area' => [
                                    'topLeft' => [
                                        'unit' => 'some unit',
                                        'x' => 1,
                                        'y' => 1,
                                    ],
                                    'bottomRight' => [
                                        'unit' => 'some unit',
                                        'x' => 10,
                                        'y' => 10,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedErrors' => [
                    'data.crops.0.name' => ['data.crops.0.name should be one of (16_9, 4_3, portrait) values.'],
                ],
            ],

            'non-string crop name' => [
                'data' => [
                    'data' => [
                        'crops' => [
                            [
                                'name' => new stdClass(),
                                'source' => 'original',
                                'area' => [
                                    'topLeft' => [
                                        'unit' => 'some unit',
                                        'x' => 1,
                                        'y' => 1,
                                    ],
                                    'bottomRight' => [
                                        'unit' => 'some unit',
                                        'x' => 10,
                                        'y' => 10,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedValidationData' => [
                    'data' => [
                        'crops' => [
                            [
                                'name' => null,
                                'source' => 'original',
                                'area' => [
                                    'topLeft' => [
                                        'unit' => 'some unit',
                                        'x' => 1,
                                        'y' => 1,
                                    ],
                                    'bottomRight' => [
                                        'unit' => 'some unit',
                                        'x' => 10,
                                        'y' => 10,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedErrors' => [
                    'data.crops.0.name' => [
                        'data.crops.0.name should be a string.',
                        'data.crops.0.name should be one of (16_9, 4_3, portrait) values.',
                    ],
                ],
            ],

            'missing crop source' => [
                'data' => [
                    'data' => [
                        'crops' => [
                            [
                                'name' => '4_3',
                                'area' => [
                                    'topLeft' => [
                                        'unit' => 'some unit',
                                        'x' => 1,
                                        'y' => 1,
                                    ],
                                    'bottomRight' => [
                                        'unit' => 'some unit',
                                        'x' => 10,
                                        'y' => 10,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedValidationData' => [
                    'data' => [
                        'crops' => [
                            [
                                'name' => '4_3',
                                'source' => null,
                                'area' => [
                                    'topLeft' => [
                                        'unit' => 'some unit',
                                        'x' => 1,
                                        'y' => 1,
                                    ],
                                    'bottomRight' => [
                                        'unit' => 'some unit',
                                        'x' => 10,
                                        'y' => 10,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedErrors' => [
                    'data.crops.0.source' => ['data.crops.0.source is required.'],
                ],
            ],

            'invalid crop source' => [
                'data' => [
                    'data' => [
                        'crops' => [
                            [
                                'name' => '4_3',
                                'source' => 'invalid',
                                'area' => [
                                    'topLeft' => [
                                        'unit' => 'some unit',
                                        'x' => 1,
                                        'y' => 1,
                                    ],
                                    'bottomRight' => [
                                        'unit' => 'some unit',
                                        'x' => 10,
                                        'y' => 10,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedValidationData' => [
                    'data' => [
                        'crops' => [
                            [
                                'name' => '4_3',
                                'source' => null,
                                'area' => [
                                    'topLeft' => [
                                        'unit' => 'some unit',
                                        'x' => 1,
                                        'y' => 1,
                                    ],
                                    'bottomRight' => [
                                        'unit' => 'some unit',
                                        'x' => 10,
                                        'y' => 10,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedErrors' => [
                    'data.crops.0.source' => ['data.crops.0.source should be one of (16_9, original) values.'],
                ],
            ],

            'non-string crop source' => [
                'data' => [
                    'data' => [
                        'crops' => [
                            [
                                'name' => '4_3',
                                'source' => new stdClass(),
                                'area' => [
                                    'topLeft' => [
                                        'unit' => 'some unit',
                                        'x' => 1,
                                        'y' => 1,
                                    ],
                                    'bottomRight' => [
                                        'unit' => 'some unit',
                                        'x' => 10,
                                        'y' => 10,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedValidationData' => [
                    'data' => [
                        'crops' => [
                            [
                                'name' => '4_3',
                                'source' => null,
                                'area' => [
                                    'topLeft' => [
                                        'unit' => 'some unit',
                                        'x' => 1,
                                        'y' => 1,
                                    ],
                                    'bottomRight' => [
                                        'unit' => 'some unit',
                                        'x' => 10,
                                        'y' => 10,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedErrors' => [
                    'data.crops.0.source' => [
                        'data.crops.0.source should be a string.',
                        'data.crops.0.source should be one of (16_9, original) values.',
                    ],
                ],
            ],

            'missing crop area topLeft unit' => [
                'data' => [
                    'data' => [
                        'crops' => [
                            [
                                'name' => '4_3',
                                'source' => 'original',
                                'area' => [
                                    'topLeft' => [
                                        'x' => 1,
                                        'y' => 1,
                                    ],
                                    'bottomRight' => [
                                        'unit' => 'some unit',
                                        'x' => 10,
                                        'y' => 10,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedValidationData' => [
                    'data' => [
                        'crops' => [
                            [
                                'name' => '4_3',
                                'source' => 'original',
                                'area' => [
                                    'topLeft' => [
                                        'unit' => null,
                                        'x' => 1,
                                        'y' => 1,
                                    ],
                                    'bottomRight' => [
                                        'unit' => 'some unit',
                                        'x' => 10,
                                        'y' => 10,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedErrors' => [
                    'data.crops.0.area.topLeft.unit' => [
                        'data.crops.0.area.topLeft.unit is required.',
                    ],
                ],
            ],

            'non-string crop area topLeft unit' => [
                'data' => [
                    'data' => [
                        'crops' => [
                            [
                                'name' => '4_3',
                                'source' => 'original',
                                'area' => [
                                    'topLeft' => [
                                        'unit' => new stdClass(),
                                        'x' => 1,
                                        'y' => 1,
                                    ],
                                    'bottomRight' => [
                                        'unit' => 'some unit',
                                        'x' => 10,
                                        'y' => 10,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedValidationData' => [
                    'data' => [
                        'crops' => [
                            [
                                'name' => '4_3',
                                'source' => 'original',
                                'area' => [
                                    'topLeft' => [
                                        'unit' => null,
                                        'x' => 1,
                                        'y' => 1,
                                    ],
                                    'bottomRight' => [
                                        'unit' => 'some unit',
                                        'x' => 10,
                                        'y' => 10,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedErrors' => [
                    'data.crops.0.area.topLeft.unit' => [
                        'data.crops.0.area.topLeft.unit should be a string.',
                    ],
                ],
            ],

            'missing crop area topLeft x' => [
                'data' => [
                    'data' => [
                        'crops' => [
                            [
                                'name' => '4_3',
                                'source' => 'original',
                                'area' => [
                                    'topLeft' => [
                                        'unit' => 'some unit',
                                        'y' => 1,
                                    ],
                                    'bottomRight' => [
                                        'unit' => 'some unit',
                                        'x' => 10,
                                        'y' => 10,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedValidationData' => [
                    'data' => [
                        'crops' => [
                            [
                                'name' => '4_3',
                                'source' => 'original',
                                'area' => [
                                    'topLeft' => [
                                        'unit' => 'some unit',
                                        'x' => null,
                                        'y' => 1,
                                    ],
                                    'bottomRight' => [
                                        'unit' => 'some unit',
                                        'x' => 10,
                                        'y' => 10,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedErrors' => [
                    'data.crops.0.area.topLeft.x' => [
                        'data.crops.0.area.topLeft.x is required.',
                    ],
                ],
            ],

            'non-float crop area topLeft x' => [
                'data' => [
                    'data' => [
                        'crops' => [
                            [
                                'name' => '4_3',
                                'source' => 'original',
                                'area' => [
                                    'topLeft' => [
                                        'unit' => 'some unit',
                                        'x' => 'string',
                                        'y' => 1,
                                    ],
                                    'bottomRight' => [
                                        'unit' => 'some unit',
                                        'x' => 10,
                                        'y' => 10,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedValidationData' => [
                    'data' => [
                        'crops' => [
                            [
                                'name' => '4_3',
                                'source' => 'original',
                                'area' => [
                                    'topLeft' => [
                                        'unit' => 'some unit',
                                        'x' => null,
                                        'y' => 1,
                                    ],
                                    'bottomRight' => [
                                        'unit' => 'some unit',
                                        'x' => 10,
                                        'y' => 10,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedErrors' => [
                    'data.crops.0.area.topLeft.x' => [
                        'data.crops.0.area.topLeft.x should be a float.',
                    ],
                ],
            ],

            'missing crop area topLeft y' => [
                'data' => [
                    'data' => [
                        'crops' => [
                            [
                                'name' => '4_3',
                                'source' => 'original',
                                'area' => [
                                    'topLeft' => [
                                        'unit' => 'some unit',
                                        'x' => 1,
                                    ],
                                    'bottomRight' => [
                                        'unit' => 'some unit',
                                        'x' => 10,
                                        'y' => 10,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedValidationData' => [
                    'data' => [
                        'crops' => [
                            [
                                'name' => '4_3',
                                'source' => 'original',
                                'area' => [
                                    'topLeft' => [
                                        'unit' => 'some unit',
                                        'x' => 1,
                                        'y' => null,
                                    ],
                                    'bottomRight' => [
                                        'unit' => 'some unit',
                                        'x' => 10,
                                        'y' => 10,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedErrors' => [
                    'data.crops.0.area.topLeft.y' => [
                        'data.crops.0.area.topLeft.y is required.',
                    ],
                ],
            ],

            'non-float crop area topLeft y' => [
                'data' => [
                    'data' => [
                        'crops' => [
                            [
                                'name' => '4_3',
                                'source' => 'original',
                                'area' => [
                                    'topLeft' => [
                                        'unit' => 'some unit',
                                        'x' => 1,
                                        'y' => 'string',
                                    ],
                                    'bottomRight' => [
                                        'unit' => 'some unit',
                                        'x' => 10,
                                        'y' => 10,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedValidationData' => [
                    'data' => [
                        'crops' => [
                            [
                                'name' => '4_3',
                                'source' => 'original',
                                'area' => [
                                    'topLeft' => [
                                        'unit' => 'some unit',
                                        'x' => 1,
                                        'y' => null,
                                    ],
                                    'bottomRight' => [
                                        'unit' => 'some unit',
                                        'x' => 10,
                                        'y' => 10,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedErrors' => [
                    'data.crops.0.area.topLeft.y' => [
                        'data.crops.0.area.topLeft.y should be a float.',
                    ],
                ],
            ],

            'missing crop area bottomRight unit' => [
                'data' => [
                    'data' => [
                        'crops' => [
                            [
                                'name' => '4_3',
                                'source' => 'original',
                                'area' => [
                                    'topLeft' => [
                                        'unit' => 'some unit',
                                        'x' => 10,
                                        'y' => 10,
                                    ],
                                    'bottomRight' => [
                                        'x' => 1,
                                        'y' => 1,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedValidationData' => [
                    'data' => [
                        'crops' => [
                            [
                                'name' => '4_3',
                                'source' => 'original',
                                'area' => [
                                    'topLeft' => [
                                        'unit' => 'some unit',
                                        'x' => 10,
                                        'y' => 10,
                                    ],
                                    'bottomRight' => [
                                        'unit' => null,
                                        'x' => 1,
                                        'y' => 1,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedErrors' => [
                    'data.crops.0.area.bottomRight.unit' => [
                        'data.crops.0.area.bottomRight.unit is required.',
                    ],
                ],
            ],

            'non-string crop area bottomRight unit' => [
                'data' => [
                    'data' => [
                        'crops' => [
                            [
                                'name' => '4_3',
                                'source' => 'original',
                                'area' => [
                                    'topLeft' => [
                                        'unit' => 'some unit',
                                        'x' => 10,
                                        'y' => 10,
                                    ],
                                    'bottomRight' => [
                                        'unit' => new stdClass(),
                                        'x' => 1,
                                        'y' => 1,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedValidationData' => [
                    'data' => [
                        'crops' => [
                            [
                                'name' => '4_3',
                                'source' => 'original',
                                'area' => [
                                    'topLeft' => [
                                        'unit' => 'some unit',
                                        'x' => 10,
                                        'y' => 10,
                                    ],
                                    'bottomRight' => [
                                        'unit' => null,
                                        'x' => 1,
                                        'y' => 1,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedErrors' => [
                    'data.crops.0.area.bottomRight.unit' => [
                        'data.crops.0.area.bottomRight.unit should be a string.',
                    ],
                ],
            ],

            'missing crop area bottomRight x' => [
                'data' => [
                    'data' => [
                        'crops' => [
                            [
                                'name' => '4_3',
                                'source' => 'original',
                                'area' => [
                                    'topLeft' => [
                                        'unit' => 'some unit',
                                        'x' => 10,
                                        'y' => 10,
                                    ],
                                    'bottomRight' => [
                                        'unit' => 'some unit',
                                        'y' => 1,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedValidationData' => [
                    'data' => [
                        'crops' => [
                            [
                                'name' => '4_3',
                                'source' => 'original',
                                'area' => [
                                    'topLeft' => [
                                        'unit' => 'some unit',
                                        'x' => 10,
                                        'y' => 10,
                                    ],
                                    'bottomRight' => [
                                        'unit' => 'some unit',
                                        'x' => null,
                                        'y' => 1,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedErrors' => [
                    'data.crops.0.area.bottomRight.x' => [
                        'data.crops.0.area.bottomRight.x is required.',
                    ],
                ],
            ],

            'non-float crop area bottomRight x' => [
                'data' => [
                    'data' => [
                        'crops' => [
                            [
                                'name' => '4_3',
                                'source' => 'original',
                                'area' => [
                                    'topLeft' => [
                                        'unit' => 'some unit',
                                        'x' => 10,
                                        'y' => 10,
                                    ],
                                    'bottomRight' => [
                                        'unit' => 'some unit',
                                        'x' => 'string',
                                        'y' => 1,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedValidationData' => [
                    'data' => [
                        'crops' => [
                            [
                                'name' => '4_3',
                                'source' => 'original',
                                'area' => [
                                    'topLeft' => [
                                        'unit' => 'some unit',
                                        'x' => 10,
                                        'y' => 10,
                                    ],
                                    'bottomRight' => [
                                        'unit' => 'some unit',
                                        'x' => null,
                                        'y' => 1,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedErrors' => [
                    'data.crops.0.area.bottomRight.x' => [
                        'data.crops.0.area.bottomRight.x should be a float.',
                    ],
                ],
            ],

            'missing crop area bottomRight y' => [
                'data' => [
                    'data' => [
                        'crops' => [
                            [
                                'name' => '4_3',
                                'source' => 'original',
                                'area' => [
                                    'topLeft' => [
                                        'unit' => 'some unit',
                                        'x' => 10,
                                        'y' => 10,
                                    ],
                                    'bottomRight' => [
                                        'unit' => 'some unit',
                                        'x' => 1,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedValidationData' => [
                    'data' => [
                        'crops' => [
                            [
                                'name' => '4_3',
                                'source' => 'original',
                                'area' => [
                                    'topLeft' => [
                                        'unit' => 'some unit',
                                        'x' => 10,
                                        'y' => 10,
                                    ],
                                    'bottomRight' => [
                                        'unit' => 'some unit',
                                        'x' => 1,
                                        'y' => null,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedErrors' => [
                    'data.crops.0.area.bottomRight.y' => [
                        'data.crops.0.area.bottomRight.y is required.',
                    ],
                ],
            ],

            'non-float crop area bottomRight y' => [
                'data' => [
                    'data' => [
                        'crops' => [
                            [
                                'name' => '4_3',
                                'source' => 'original',
                                'area' => [
                                    'topLeft' => [
                                        'unit' => 'some unit',
                                        'x' => 10,
                                        'y' => 10,
                                    ],
                                    'bottomRight' => [
                                        'unit' => 'some unit',
                                        'x' => 1,
                                        'y' => 'string',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedValidationData' => [
                    'data' => [
                        'crops' => [
                            [
                                'name' => '4_3',
                                'source' => 'original',
                                'area' => [
                                    'topLeft' => [
                                        'unit' => 'some unit',
                                        'x' => 10,
                                        'y' => 10,
                                    ],
                                    'bottomRight' => [
                                        'unit' => 'some unit',
                                        'x' => 1,
                                        'y' => null,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedErrors' => [
                    'data.crops.0.area.bottomRight.y' => [
                        'data.crops.0.area.bottomRight.y should be a float.',
                    ],
                ],
            ],
        ];
    }

    /**
     * @param array $rules
     * @param array $data
     * @param array $expectedValidationData
     * @param array $expectedErrors
     *
     * @dataProvider validationDataProvider
     */
    public function testValidate(
        array $rules,
        array $data,
        array $expectedValidationData,
        array $expectedErrors
    ): void {
        $validator = $this->createTestValidator($rules);

        $result = $validator->validate($data);

        $this->assertEquals(
            $expectedErrors,
            $result->getErrors(),
            'Errors do not match expected.'
        );

        $this->assertEquals(
            $expectedValidationData,
            $result->getValidatedData(),
            'Validation data do not match expected.'
        );
    }

    public function validationDataProvider(): array
    {
        $dateTime = new DateTime();

        return [
            'every value is required within key' => [
                'rules' => [
                    'key.*' => 'required',
                ],
                'data' => [
                    'key' => [
                        'value',
                    ],
                ],
                'expectedValidationData' => [
                    'key' => [
                        'value',
                    ],
                ],
                'expectedErrors' => [],
            ],

            'every test21 key in each array is required' => [
                'rules' => [
                    '*.test21' => 'required',
                ],
                'data' => [
                    [
                        'test21' => 'value',
                    ],
                ],
                'expectedValidationData' => [
                    [
                        'test21' => 'value',
                    ],
                ],
                'expectedErrors' => [],
            ],

            'every element should be int' => [
                'rules' => [
                    '*' => 'int',
                ],
                'data' => [
                    1,
                    2,
                    'test21' => 1,
                    '*' => 3,
                ],
                'expectedValidationData' => [
                    1,
                    2,
                    'test21' => 1,
                    '*' => 3,
                ],
                'expectedErrors' => [],
            ],

            'every key1 is required in each array in test22' => [
                'rules' => [
                    'test22.*.key1' => 'required',
                ],
                'data' => [
                    'test22' => [
                        [
                            'key1' => 'value',
                        ],
                    ],
                ],
                'expectedValidationData' => [
                    'test22' => [
                        [
                            'key1' => 'value',
                        ],
                    ],
                ],
                'expectedErrors' => [],
            ],

            'any value of key1 is required in each array of test23' => [
                'rules' => [
                    'test23.*.key1.*' => 'required',
                ],
                'data' => [
                    'test23' => [
                        [
                            'key1' => [
                                'value1',
                                'value2',
                            ],
                        ],
                    ],
                ],
                'expectedValidationData' => [
                    'test23' => [
                        [
                            'key1' => [
                                'value1',
                                'value2',
                            ],
                        ],
                    ],
                ],
                'expectedErrors' => [],
            ],

            'any value of key1 and key2 is required in each array of test24' => [
                'rules' => [
                    'test24.*.key1.*' => 'required',
                    'test24.*.key2.*' => 'required',
                ],
                'data' => [
                    'test24' => [
                        [
                            'key1' => [
                                'value1',
                            ],
                            'key2' => [
                                'value1',
                            ],
                        ],
                    ],
                ],
                'expectedValidationData' => [
                    'test24' => [
                        [
                            'key1' => [
                                'value1',
                            ],
                            'key2' => [
                                'value1',
                            ],
                        ],
                    ],
                ],
                'expectedErrors' => [],
            ],

            'every value of multidimensional array should be int' => [
                'rules' => [
                    'key1.*' => 'int',
                    'key2.*' => 'int',
                ],
                'data' => [
                    'key1' => [
                        123,
                        555,
                    ],
                    'key2' => [
                        'not correct',
                        123,
                        true,
                    ],
                ],
                'expectedValidationData' => [
                    'key1' => [
                        123,
                        555,
                    ],
                    'key2' => [
                        1 => 123,
                    ],
                ],
                'expectedErrors' => [
                    'key2.0' => ['key2.0 should be an integer.'],
                    'key2.2' => ['key2.2 should be an integer.'],
                ],
            ],

            'every value of multidimensional array should be int and min:10' => [
                'rules' => [
                    'test4.key1.key2.*' => 'int|min:10',
                    'test5.key1.key2.*' => 'int|min:10',
                    'test6.key1.key2.*' => 'int|min:10',
                ],
                'data' => [
                    'test4' => 3,
                    'test5' => [
                        'key1' => [
                            'key2' => ['20', 3],
                        ],
                    ],
                    'test6' => [
                        'key1' => [
                            'key2' => [15],
                        ],
                    ],
                ],
                'expectedValidationData' => [
                    'test4' => [
                        'key1' => [
                            'key2' => [],
                        ],
                    ],
                    'test5' => [
                        'key1' => [
                            'key2' => ['20'],
                        ],
                    ],
                    'test6' => [
                        'key1' => [
                            'key2' => [15],
                        ],
                    ],
                ],
                'expectedErrors' => [
                    'test5.key1.key2.1' => ['test5.key1.key2.1 should be greater or equal to 10.'],
                ],
            ],

            'every value should be in:tesla,mustang but sent empty input' => [
                'rules' => [
                    'test10.key1.key2.*' => 'in:tesla,mustang',
                ],
                'data' => [],
                'expectedValidationData' => [
                    'test10' => [
                        'key1' => [
                            'key2' => [],
                        ],
                    ],
                ],
                'expectedErrors' => [],
            ],

            'every value should be int in deep array' => [
                'rules' => [
                    'test14.key1.key2.key3.key4.keyN.*' => 'int',
                    'test15.key1.key2.key3.key4.keyN.*' => 'int',
                    'test16.key1.key2.key3.key4.keyN.*' => 'int',
                ],
                'data' => [
                    'test14' => ['key1' => 123],
                    'test15' => ['key1' => []],
                    'test16' => [
                        'key1' => [
                            'key2' => [
                                'key3' => [
                                    'key4' => [
                                        'keyN' => [1, 2, 3],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedValidationData' => [
                    'test14' => [
                        'key1' => [
                            'key2' => [
                                'key3' => [
                                    'key4' => [
                                        'keyN' => [],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'test15' => [
                        'key1' => [
                            'key2' => [
                                'key3' => [
                                    'key4' => [
                                        'keyN' => [],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'test16' => [
                        'key1' => [
                            'key2' => [
                                'key3' => [
                                    'key4' => [
                                        'keyN' => [1, 2, 3],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedErrors' => [],
            ],

            'every field should be string + some field specified' => [
                'rules' => [
                    'key.*' => 'string',
                    'key.key1' => 'string',
                    'key.key2' => 'string',
                ],
                'data' => [
                    'key' => ['some string'],
                ],
                'expectedValidationData' => [
                    'key' => [
                        'some string',
                        'key1' => null,
                        'key2' => null,
                    ],
                ],
                'expectedErrors' => [],
            ],

            'each field is required and specified' => [
                'rules' => [
                    'key.user.firstName' => 'required|string',
                    'key.user.lastName' => 'required|string',
                    'key.user.email' => 'required|email',
                    'key.user.age' => 'required|int',
                ],
                'data' => [
                    'key' => [
                        'user' => [
                            'firstName' => 'first name',
                            'lastName' => 'last name',
                            'email' => 'email@email.com',
                            'age' => 12,
                        ],
                    ],
                ],
                'expectedValidationData' => [
                    'key' => [
                        'user' => [
                            'firstName' => 'first name',
                            'lastName' => 'last name',
                            'email' => 'email@email.com',
                            'age' => 12,
                        ],
                    ],
                ],
                'expectedErrors' => [],
            ],

            'every value is required + each field specified' => [
                'rules' => [
                    'key.user.*' => 'required',
                    'key.user.firstName' => 'string',
                    'key.user.lastName' => 'string',
                ],
                'data' => [
                    'key' => [
                        'user' => [
                            'firstName' => 'first name',
                            'lastName' => 'last name',
                        ],
                    ],
                ],
                'expectedValidationData' => [
                    'key' => [
                        'user' => [
                            'firstName' => 'first name',
                            'lastName' => 'last name',
                        ],
                    ],
                ],
                'expectedErrors' => [
                ],
            ],

            'every value should be arrayOf:int' => [
                'rules' => [
                    'key0.*' => 'arrayOf:int',
                    'key1.*' => 'arrayOf:int',
                ],
                'data' => [
                    'key0' => [
                        'a' => ['5', 6, 'string'],
                        'b' => ['5', 6],
                        'c' => ['5', 6, 'string'],
                    ],
                    'key1' => [
                        'a' => ['5', 6],
                    ],
                ],
                'expectedValidationData' => [
                    'key0' => [
                        'a' => [],
                        'b' => ['5', 6],
                        'c' => [],
                    ],
                    'key1' => [
                        'a' => ['5', 6],
                    ],
                ],
                'expectedErrors' => [
                    'key0.a' => ['key0.a should be an array of int.'],
                    'key0.c' => ['key0.c should be an array of int.'],
                ],
            ],

            'key.* is required and should be an array' => [
                'rules' => [
                    'key0.*' => 'required|array',
                    'key1.*' => 'required|array',
                    'key2.*' => 'required|array',
                ],
                'data' => [
                    'key0' => [
                        'a' => ['5', 6, 'string'],
                        'b' => ['5', 6],
                        'c' => ['5', 6, 'string'],
                    ],
                    'key1' => [
                        'a' => 'not an array',
                    ],
                    'key2' => true,
                ],
                'expectedValidationData' => [
                    'key0' => [
                        'a' => ['5', 6, 'string'],
                        'b' => ['5', 6],
                        'c' => ['5', 6, 'string'],
                    ],
                    'key1' => [],
                    'key2' => [],
                ],
                'expectedErrors' => [
                    'key1.a' => ['key1.a should be an array.'],
                    'key2.0' => ['key2.0 is required.'],
                ],
            ],

            'key.*.key.*.key is required' => [
                'rules' => [
                    'test26.*.names.*.first' => 'required',
                    'test27.*.names.*.first' => 'required',
                    'test28.*.names.*.first' => 'required',
                    'test29.*.names.*.first' => 'required',
                    'test30.*.names.*.first' => 'required',
                ],
                'data' => [
                    'test26' => [],
                    'test27' => [
                        [
                            'names' => [
                                [
                                    'first' => 'firstName',
                                ],
                                [
                                    'first' => 'firstName2',
                                ],
                            ],
                        ],
                        [
                            'names' => [
                                [
                                    'first' => 'firstName',
                                ],
                            ],
                        ],
                    ],
                    'test28' => [
                        [
                            'names' => [
                                [
                                    'first' => 'firstName',
                                ],
                                [
                                    'first' => 'firstName2',
                                ],
                            ],
                        ],
                        [
                            'names' => [],
                        ],
                    ],
                    'test29' => [
                        [
                            'names' => [
                                [
                                    'first' => 'firstName',
                                ],
                                [
                                    'first' => 'firstName2',
                                ],
                            ],
                        ],
                        [
                            'names' => [
                                [
                                    'first' => 'firstName',
                                ],
                                [],
                            ],
                        ],
                        [
                            'names' => [],
                        ],
                    ],
                    'test30' => [
                        [
                            'names' => [
                                [
                                    'first' => 'firstName',
                                ],
                                [
                                    'first' => 'firstName2',
                                ],
                            ],
                        ],
                        [
                            'names' => [
                                [],
                                [
                                    'first' => 'firstName',
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedValidationData' => [
                    'test26' => [
                        [
                            'names' => [
                                [
                                    'first' => null,
                                ],
                            ],
                        ],
                    ],
                    'test27' => [
                        [
                            'names' => [
                                [
                                    'first' => 'firstName',
                                ],
                                [
                                    'first' => 'firstName2',
                                ],
                            ],
                        ],
                        [
                            'names' => [
                                [
                                    'first' => 'firstName',
                                ],
                            ],
                        ],
                    ],
                    'test28' => [
                        [
                            'names' => [
                                [
                                    'first' => 'firstName',
                                ],
                                [
                                    'first' => 'firstName2',
                                ],
                            ],
                        ],
                        [
                            'names' => [
                                [
                                    'first' => null,
                                ],
                            ],
                        ],
                    ],
                    'test29' => [
                        [
                            'names' => [
                                [
                                    'first' => 'firstName',
                                ],
                                [
                                    'first' => 'firstName2',
                                ],
                            ],
                        ],
                        [
                            'names' => [
                                [
                                    'first' => 'firstName',
                                ],
                                [
                                    'first' => null,
                                ],
                            ],
                        ],
                        [
                            'names' => [
                                [
                                    'first' => null,
                                ],
                            ],
                        ],
                    ],
                    'test30' => [
                        [
                            'names' => [
                                [
                                    'first' => 'firstName',
                                ],
                                [
                                    'first' => 'firstName2',
                                ],
                            ],
                        ],
                        [
                            'names' => [
                                [
                                    'first' => null,
                                ],
                                [
                                    'first' => 'firstName',
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedErrors' => [
                    'test26.0.names.0.first' => ['test26.0.names.0.first is required.'],
                    'test28.1.names.0.first' => ['test28.1.names.0.first is required.'],
                    'test29.1.names.1.first' => ['test29.1.names.1.first is required.'],
                    'test29.2.names.0.first' => ['test29.2.names.0.first is required.'],
                    'test30.1.names.0.first' => ['test30.1.names.0.first is required.'],
                ],
            ],

            'different array + every value combinations' => [
                'rules' => [
                    'test22' => 'array',
                    'test22.*' => 'int',
                    'test23' => 'array',
                    'test23.*' => 'int',
                ],
                'data' => [
                    'test22' => 'not-array',
                    'test23' => [
                        1,
                        2,
                        3,
                    ],
                ],
                'expectedValidationData' => [
                    'test22' => [],
                    'test23' => [1, 2, 3],
                ],
                'expectedErrors' => [
                    'test22' => ['test22 should be an array.'],
                ],
            ],

            'key.*.key is required' => [
                'rules' => [
                    'test24.*.name' => 'required',
                    'test25.*.name' => 'required',
                ],
                'data' => [
                    'test24' => [],
                    'test25' => [
                        [
                            'name' => 'foo',
                        ],
                    ],
                ],
                'expectedValidationData' => [
                    'test24' => [
                        [
                            'name' => null,
                        ],
                    ],
                    'test25' => [
                        [
                            'name' => 'foo',
                        ],
                    ],
                ],
                'expectedErrors' => [
                    'test24.0.name' => ['test24.0.name is required.'],
                ],
            ],

            'key.* when middle value is correct' => [
                'rules' => [
                    'test8.key1.*' => 'min:10',
                ],
                'data' => [
                    'test8' => ['key1' => [3, 11, 7]],
                ],
                'expectedValidationData' => [
                    'test8' => ['key1' => [1 => 11]],
                ],
                'expectedErrors' => [
                    'test8.key1.0' => ['test8.key1.0 should be greater or equal to 10.'],
                    'test8.key1.2' => ['test8.key1.2 should be greater or equal to 10.'],
                ],
            ],

            'key.* when last value is correct' => [
                'rules' => [
                    'test12.key1.key2.*' => 'email',
                ],
                'data' => [
                    'test12' => [
                        'key1' => [
                            'key2' => ['wrong email', 'correct@email.com'],
                        ],
                    ],
                ],
                'expectedValidationData' => [
                    'test12' => [
                        'key1' => [
                            'key2' => [1 => 'correct@email.com'],
                        ],
                    ],
                ],
                'expectedErrors' => [
                    'test12.key1.key2.0' => ['test12.key1.key2.0 should be a valid email address.'],
                ],
            ],

            'key.key is required and should be int' => [
                'rules' => [
                    'test0.key1' => 'required|int',
                ],
                'data' => [
                    'test0' => ['key1' => 0],
                ],
                'expectedValidationData' => [
                    'test0' => ['key1' => 0],
                ],
                'expectedErrors' => [],
            ],

            'key should be arrayOf:int' => [
                'rules' => [
                    'test1' => 'arrayOf:int',
                ],
                'data' => [],
                'expectedValidationData' => [
                    'test1' => null,
                ],
                'expectedErrors' => [],
            ],

            'key.key(.key) should be in:tesla,mustang' => [
                'rules' => [
                    'test1.key1.key2' => 'in:tesla,mustang',
                    'test2.key1' => 'in:tesla,mustang',
                    'test3.key1' => 'in:tesla,mustang',
                ],
                'data' => [
                    'test1' => [
                        'key1' => 'bmw',
                    ],
                    'test2' => [
                        'key1' => 'bmw',
                    ],
                    'test3' => [
                        'key1' => 'tesla',
                    ],
                ],
                'expectedValidationData' => [
                    'test1' => [
                        'key1' => [
                            'key2' => null,
                        ],
                    ],
                    'test2' => [
                        'key1' => null,
                    ],
                    'test3' => [
                        'key1' => 'tesla',
                    ],
                ],
                'expectedErrors' => [
                    'test2.key1' => ['test2.key1 should be one of (tesla, mustang) values.'],
                ],
            ],

            'every value validation with valid values' => [
                'rules' => [
                    'test1.*' => 'required|in:testValue1,testValue2,testValue3',
                    'test2.*' => 'required|int',
                    'test3.*' => 'required|instanceOf:\DateTime',
                    'test4.*' => 'required|min:5|max:10',
                    'test5.*' => 'required|email',
                    'test6.*' => 'string',
                    'test7.*' => 'required|string',
                ],
                'data' => [
                    'test1' => ['testValue1', 'testValue2'],
                    'test2' => [1, 2, 5],
                    'test3' => [$dateTime],
                    'test4' => [5, 6, 9, 7],
                    'test5' => ['test1@test.com', 'test2@test.com'],
                    'test7' => ['value'],
                ],
                'expectedValidationData' => [
                    'test1' => ['testValue1', 'testValue2'],
                    'test2' => [1, 2, 5],
                    'test3' => [$dateTime],
                    'test4' => [5, 6, 9, 7],
                    'test5' => ['test1@test.com', 'test2@test.com'],
                    'test6' => [],
                    'test7' => ['value'],
                ],
                'expectedErrors' => [],
            ],

            'every value validation with invalid values' => [
                'rules' => [
                    'test1.*' => 'required|in:testValue1,testValue2,testValue3',
                    'test2.*' => 'required|int',
                    'test3.*' => 'required|instanceOf:\DateTime',
                    'test4.*' => 'required|min:5|max:10',
                    'test5.*' => 'required|email',
                    'test6.*' => 'string',
                    'test7.*' => 'required|string',
                ],
                'data' => [
                    'test1' => ['testValue1', 'testValue4'],
                    'test2' => [1, 2, 5.3],
                    'test3' => [$dateTime, 'not a date time object'],
                    'test4' => [3, 6, 9, 12],
                    'test5' => ['test1@test.com', 'notCorrectEmail'],
                    'test6' => 'test1',
                ],
                'expectedValidationData' => [
                    'test1' => ['testValue1'],
                    'test2' => [1, 2],
                    'test3' => [$dateTime],
                    'test4' => [3, 6, 9],
                    'test5' => ['test1@test.com'],
                    'test6' => [],
                    'test7' => [],
                ],
                'expectedErrors' => [
                    'test1.1' => ['test1.1 should be one of (testValue1, testValue2, testValue3) values.'],
                    'test2.2' => ['test2.2 should be an integer.'],
                    'test3.1' => ['test3.1 should be an instance of \DateTime.'],
                    'test4.0' => ['test4.0 should be greater or equal to 5.'],
                    'test4.3' => ['test4.3 should be lower or equal to 10.'],
                    'test5.1' => ['test5.1 should be a valid email address.'],
                    'test7.0' => ['test7.0 is required.'],
                ],
            ],

            'key.key should be an array, got multidimensional array + irrelevant field' => [
                'rules' => [
                    'test14.key2' => 'array',
                ],
                'data' => [
                    'test14' => [
                        'key1' => 'irrelevant field',
                        'key2' => [
                            [],
                        ],
                    ],
                ],
                'expectedValidationData' => [
                    'test14' => [
                        'key2' => [
                            [],
                        ],
                    ],
                ],
                'expectedErrors' => [],
            ],

            'key.key1 and key.key2 is required if one of them exists - both provided' => [
                'rules' => [
                    'key.key1' => 'requiredIfExist:key.key2',
                    'key.key2' => 'requiredIfExist:key.key1',
                ],
                'data' => [
                    'key' => [
                        'key1' => 'value',
                        'key2' => 'value',
                    ],
                ],
                'expectedValidationData' => [
                    'key' => [
                        'key1' => 'value',
                        'key2' => 'value',
                    ],
                ],
                'expectedErrors' => [],
            ],

            'key.key1 and key.key2 is required if one of them exists - key1 provided' => [
                'rules' => [
                    'key.key1' => 'requiredIfExist:key.key2',
                    'key.key2' => 'requiredIfExist:key.key1',
                ],
                'data' => [
                    'key' => [
                        'key1' => 'value',
                    ],
                ],
                'expectedValidationData' => [
                    'key' => [
                        'key1' => 'value',
                        'key2' => null,
                    ],
                ],
                'expectedErrors' => [
                    'key.key2' => ['key.key2 is required because you provided key.key1.'],
                ],
            ],

            'key.key1 and key.key2 is required if one of them exists - key2 provided' => [
                'rules' => [
                    'key.key1' => 'requiredIfExist:key.key2',
                    'key.key2' => 'requiredIfExist:key.key1',
                ],
                'data' => [
                    'key' => [
                        'key2' => 'value',
                    ],
                ],
                'expectedValidationData' => [
                    'key' => [
                        'key1' => null,
                        'key2' => 'value',
                    ],
                ],
                'expectedErrors' => [
                    'key.key1' => ['key.key1 is required because you provided key.key2.'],
                ],
            ],

            'key.key1 and key.key2 is required if one of them exists - none provided' => [
                'rules' => [
                    'key.key1' => 'requiredIfExist:key.key2',
                    'key.key2' => 'requiredIfExist:key.key1',
                ],
                'data' => [
                    'key' => [],
                ],
                'expectedValidationData' => [
                    'key' => [
                        'key1' => null,
                        'key2' => null,
                    ],
                ],
                'expectedErrors' => [],
            ],

            'key.key1 and key.key2 is required if one of them exists - none provided, not even key' => [
                'rules' => [
                    'key.key1' => 'requiredIfExist:key.key2',
                    'key.key2' => 'requiredIfExist:key.key1',
                ],
                'data' => [],
                'expectedValidationData' => [
                    'key' => [
                        'key1' => null,
                        'key2' => null,
                    ],
                ],
                'expectedErrors' => [],
            ],

            'different rule keys without matching input' => [
                'rules' => [
                    'test7.key1' => 'required|string',
                    'test8.key1' => 'required|string:strict',
                    'test9.key1' => 'required|string:strict',
                    'test10.key1.key2.*' => 'in:tesla,mustang',
                    'test11.key1.key2' => 'string',
                    'test12.key1.key2' => 'string',
                    'test13.key1.key2' => 'string',
                ],
                'data' => [
                    'test7' => ['string'],
                    'test8' => ['key1' => 123],
                    'test9' => ['key1' => 'string'],
                    'test11' => ['keyNotDefined' => []],
                    'test12' => ['key1' => ''],
                    'test13' => [],
                ],
                'expectedValidationData' => [
                    'test7' => [
                        'key1' => null,
                    ],
                    'test8' => [
                        'key1' => null,
                    ],
                    'test9' => [
                        'key1' => 'string',
                    ],
                    'test10' => [
                        'key1' => [
                            'key2' => [],
                        ],
                    ],
                    'test11' => [
                        'key1' => [
                            'key2' => null,
                        ],
                    ],
                    'test12' => [
                        'key1' => [
                            'key2' => null,
                        ],
                    ],
                    'test13' => [
                        'key1' => [
                            'key2' => null,
                        ],
                    ],
                ],
                'expectedErrors' => [
                    'test7.key1' => ['test7.key1 is required.'],
                    'test8.key1' => ['test8.key1 should be a string.'],
                ],
            ],
        ];
    }

    public function testIfDefaultValuesAreSetWhenDataIsNotProvided()
    {
        $validator = $this->createTestValidator(
            [
                'test1' => 'string',
                'test2.*' => 'int',
                'test3.key1' => 'bool',
                'test4.key1.*' => 'min:10',
                'test5.key1.key2' => 'max:100',
                'test6.key1.key2.*' => 'email',
                'test7.key1.*' => 'int',
                'test7.key2.*' => 'int',
                'test8' => 'arrayOf:int',
                'test9.*' => 'int',
                'test10.*' => 'array',
            ]
        );

        $result = $validator->validate([]);

        $this->assertTrue($result->isValid());
        $this->assertEquals(
            [
                'test1' => null,
                'test2' => [],
                'test3' => [
                    'key1' => null,
                ],
                'test4' => [
                    'key1' => [],
                ],
                'test5' => [
                    'key1' => [
                        'key2' => null,
                    ],
                ],
                'test6' => [
                    'key1' => [
                        'key2' => [],
                    ],
                ],
                'test7' => [
                    'key1' => [],
                    'key2' => [],
                ],
                'test8' => null,
                'test9' => [],
                'test10' => [],
            ],
            $result->getValidatedData()
        );
    }

    public function testIfValidatedDataIsNotSetWhenTheValidationIsNotPassed(): void
    {
        $validator = $this->createTestValidator(
            [
                'test1' => 'string:strict',
                'test2' => 'string:strict',
                'test3.*' => 'int',
                'test4.*' => 'int',
                'test5.key1' => 'bool',
                'test6.key1' => 'bool',
                'test7.key1.*' => 'min:10',
                'test8.key1.*' => 'min:10',
                'test9.key1.key2' => 'max:100',
                'test10.key1.key2' => 'max:100',
                'test11.key1.key2.*' => 'email',
                'test12.key1.key2.*' => 'email',
                'test13.key1.key2.*' => 'required',
                'test14.key1.key2.*' => 'required',
            ]
        );

        $result = $validator->validate(
            [
                'test1' => 'correct',
                'test2' => 111,
                'test3' => [123, 555],
                'test4' => ['not correct', 123, true],
                'test5' => ['key1' => true],
                'test6' => ['key1' => 'not correct'],
                'test7' => ['key1' => [12, 100]],
                'test8' => ['key1' => [3, 7]],
                'test9' => ['key1' => ['key2' => 50]],
                'test10' => ['key1' => ['key2' => 200]],
                'test11' => ['key1' => ['key2' => ['email@email.com', 'email2@email.com']]],
                'test12' => ['key1' => ['key2' => ['wrong email', 'correct@email.com']]],
                'test13' => ['key1' => ['key2' => [1, 2, 5]]],
                'test14' => ['key1' => []],
            ]
        );

        $this->assertFalse($result->isValid());
        $this->assertEquals(
            [
                'test1' => 'correct',
                'test2' => null,
                'test3' => [123, 555],
                'test4' => [1 => 123],
                'test5' => ['key1' => true],
                'test6' => ['key1' => null],
                'test7' => ['key1' => [12, 100]],
                'test8' => ['key1' => []],
                'test9' => ['key1' => ['key2' => 50]],
                'test10' => ['key1' => ['key2' => null]],
                'test11' => ['key1' => ['key2' => ['email@email.com', 'email2@email.com']]],
                'test12' => ['key1' => ['key2' => [1 => 'correct@email.com']]],
                'test13' => ['key1' => ['key2' => [1, 2, 5]]],
                'test14' => ['key1' => ['key2' => []]],
            ],
            $result->getValidatedData()
        );
        $this->assertEquals(
            [
                'test2' => ['test2 should be a string.'],
                'test4.0' => ['test4.0 should be an integer.'],
                'test4.2' => ['test4.2 should be an integer.'],
                'test6.key1' => ['test6.key1 should be a boolean.'],
                'test8.key1.0' => ['test8.key1.0 should be greater or equal to 10.'],
                'test8.key1.1' => ['test8.key1.1 should be greater or equal to 10.'],
                'test10.key1.key2' => ['test10.key1.key2 should be lower or equal to 100.'],
                'test12.key1.key2.0' => ['test12.key1.key2.0 should be a valid email address.'],
                'test14.key1.key2.0' => ['test14.key1.key2.0 is required.'],
            ],
            $result->getErrors()
        );
    }

    public function testItSucceedWhenValidatingEmptyDataMatchingTheRules(): void
    {
        $validator = $this->createTestValidator(
            [
                'test1' => 'float',
                'test2' => 'int',
                'test3' => 'string',
            ]
        );

        $result = $validator->validate(
            [
                'test1' => null,
                'test2' => null,
                'test3' => null,
            ]
        );

        $this->assertTrue($result->isValid());
        $this->assertEmpty($result->getErrors());
    }

    public function testItFailedWhenValidatingDataNotMatchingTheRules(): void
    {
        $validator = $this->createTestValidator(
            [
                'test1' => 'required',
                'test2' => 'required|int|max:2',
                'test3' => 'instanceOf:\DateTime',
                'test4' => 'arrayOf:int',
                'test5' => 'requiredIfNotExist:test3,test4',
                'test6' => 'requiredIfNotExist:test3,test4',
                'test7' => 'greaterThan:test6',
                'test8' => 'int|greaterThanOrEqualTo:test6',
                'test9' => 'int|lessThanOrEqualTo:test6',
                'test10' => 'int|lessThan:test6',
                'password' => 'required|string',
                'passwordConfirmation' => 'required|string|sameAs:password',
                'dateFrom' => 'dateTimeString',
                'dateTo' => 'dateTimeString|greaterThan:dateFrom',
                'date' => 'dateTimeString',
            ]
        );

        $result = $validator->validate(
            [
                'test2' => 'test',
                'test3' => 'not a DateTime',
                'test4' => ['5', '6', 'string'],
                'test6' => 3,
                'test7' => 3,
                'test8' => 2,
                'test9' => 4,
                'test10' => 4,
                'password' => 'test',
                'passwordConfirmation' => 'failed',
                'dateFrom' => '2005-08-15T15:52:01+00:00',
                'dateTo' => '2005-08-14T15:52:01+00:00',
                'date' => '2005-08-16 05:52:01',
            ]
        );

        $this->assertFalse($result->isValid());
        $this->assertEquals(
            [
                'test1' => ['test1 is required.'],
                'test2' => [
                    'test2 should be an integer.',
                    'test2 should be lower or equal to 2.',
                ],
                'test3' => ['test3 should be an instance of \DateTime.'],
                'test4' => ['test4 should be an array of int.'],
                'test7' => ['test7 should be greater than test6.'],
                'test8' => ['test8 should be greater than or equal to test6.'],
                'test9' => ['test9 should be less than or equal to test6.'],
                'test10' => ['test10 should be less than test6.'],
                'passwordConfirmation' => ['The field passwordConfirmation is not the same as password.'],
                'dateTo' => ['dateTo should be greater than dateFrom.'],
                'date' => ['date should be a valid datetime in "Y-m-d\TH:i:sP" format.'],
            ],
            $result->getErrors()
        );
    }

    /**
     * @param array $rules
     *
     * @dataProvider invalidRuleDataProvider
     */
    public function testSpecialRuleSeparatorsInRegexpAsStringRule(array $rules): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/^\w+ constraint cannot be used in string rules\.$/');

        $validator = $this->createTestValidator($rules);

        $validator->validate([]);
    }

    public function invalidRuleDataProvider(): array
    {
        return [
            'regexp as only rule' => [
                'rules' => [
                    'test1' => 'regexp:/[A,e](?:nt|pple)/',
                ],
            ],
            'regexp starting rule' => [
                'rules' => [
                    'test1' => 'regexp:/[A,e](?:nt|pple)/|maxLength:5',
                ],
            ],
            'regexp rule in the middle' => [
                'rules' => [
                    'test1' => 'minLength:3|regexp:/[A,e](?:nt|pple)/|maxLength:5',
                ],
            ],
            'regexp rule in the end' => [
                'rules' => [
                    'test1' => 'minLength:3|regexp:/[A,e](?:nt|pple)/',
                ],
            ],
        ];
    }

    /**
     * @param array $rules
     * @param array $data
     * @param array $expectedValidationData
     * @param array $expectedErrors
     *
     * @dataProvider rulesAsArrayDataProvider
     */
    public function testRulesAsArray(
        array $rules,
        array $data,
        array $expectedValidationData,
        array $expectedErrors
    ): void {
        $validator = $this->createTestValidator($rules);

        $result = $validator->validate($data);

        $this->assertEquals($expectedErrors, $result->getErrors());
        $this->assertEquals($expectedValidationData, $result->getValidatedData());
    }

    public function rulesAsArrayDataProvider(): array
    {
        return [
            'test1' => [
                'rules' => [
                    'test1' => ['string', 'regexp:/A(?:nt|pple)/'],
                ],
                'data' => [
                    'test1' => 'Apple',
                ]
                ,
                'expectedValidationData' => [
                    'test1' => 'Apple',
                ],
                'expectedErrors' => [],
            ],

            'test2' => [
                'rules' => [
                    'test1' => ['string', 'regexp:/A(?:nt|pple)/'],
                ],
                'data' => [
                    'test1' => '1234',
                ],
                'expectedValidationData' => [
                    'test1' => null,
                ],
                'expectedErrors' => [
                    'test1' => [
                        'test1 should match regular expression: /A(?:nt|pple)/',
                    ],
                ],
            ],
        ];
    }

    /**
     * @param array $rules
     * @param array $data
     * @param array $expectedValidationData
     * @param array $expectedErrors
     *
     * @dataProvider normalizedDataProvider
     */
    public function testItCreatesNormalizedDataBeforeGettingIntoAction(
        array $rules,
        array $data,
        array $expectedValidationData,
        array $expectedErrors
    ): void {
        $validator = $this->createTestValidator($rules);

        $result = $validator->validate($data);

        $this->assertEquals(
            $expectedValidationData,
            $result->getValidatedData(),
            'Validation data do not match expected.'
        );

        $this->assertEquals(
            $expectedErrors,
            $result->getErrors(),
            'Errors do not match expected.'
        );
    }

    public function normalizedDataProvider(): array
    {
        return [
            'test1' => [
                'rules' => [
                    'test1' => 'requiredIfExist:test3',
                    'test3' => 'requiredIfExist:test1',
                ],
                'data' => [],
                'expectedValidationData' => [
                    'test1' => null,
                    'test3' => null,
                ],
                'expectedErrors' => [],
            ],

            'test2' => [
                'rules' => [
                    'test1' => 'requiredIfExist:test3',
                    'test3' => 'requiredIfExist:test1',
                ],
                'data' => [
                    'test1' => 'some',
                    'test3' => 'value',
                ],
                'expectedValidationData' => [
                    'test1' => 'some',
                    'test3' => 'value',
                ],
                'expectedErrors' => [],
            ],

            'test3' => [
                'rules' => [
                    'test1' => 'requiredIfExist:test3',
                    'test3' => 'requiredIfExist:test1',
                ],
                'data' => ['test3' => 'some'],
                'expectedValidationData' => [
                    'test1' => null,
                    'test3' => 'some',
                ],
                'expectedErrors' => [
                    'test1' => ['test1 is required because you provided test3.'],
                ],
            ],

            'test4' => [
                'rules' => [
                    'test1' => 'requiredIfExist:test3',
                    'test3' => 'requiredIfExist:test1',
                ],
                'data' => [
                    'test1' => 'some',
                ],
                'expectedValidationData' => [
                    'test1' => 'some',
                    'test3' => null,
                ],
                'expectedErrors' => [
                    'test3' => ['test3 is required because you provided test1.'],
                ],
            ],

            'test5' => [
                'rules' => [
                    'test1' => 'required',
                    'test3' => 'string',
                    'test12.*' => 'string',
                ],
                'data' => [
                    'test1' => 'data',
                ],
                'expectedValidationData' => [
                    'test1' => 'data',
                    'test3' => null,
                    'test12' => [],
                ],
                'expectedErrors' => [],
            ],

            'test6' => [
                'rules' => [
                    'test1' => 'required',
                    'test3' => 'string',
                    'test12.*' => 'string',
                ],
                'data' => [
                    'test3' => 'data',
                ],
                'expectedValidationData' => [
                    'test1' => null,
                    'test3' => 'data',
                    'test12' => [],
                ],
                'expectedErrors' => [
                    'test1' => ['test1 is required.'],
                ],
            ],
        ];
    }

    public function testRulesCanBeSetWhenExtendingValidator(): void
    {
        $rules = ['key' => 'required'];
        $constraintCollection = new ConstraintCollection();
        $constraintCollection
            ->add(new RequiredConstraint())
            ->add(new StringConstraint());

        $validator = new class($constraintCollection, new RuleNormalizer(), $rules) extends Validator {

            public function validate(array $data): ValidationResult
            {
                $validationResult = parent::validate($data);

                Assert::assertFalse($validationResult->isValid());

                $this->setRules(['key' => 'string']);

                $validationResult = parent::validate($data);

                Assert::assertTrue($validationResult->isValid());

                return $validationResult;
            }
        };

        $validator->validate([]);
    }

    public function testItPassesEmptyParamsIfNoContextIsProvided(): void
    {
        $constraint = new class implements ConstraintInterface {
            public static function name(): string
            {
                return 'test';
            }

            public function validate($value, array $params = [], array $allData = []): bool
            {
                Assert::assertEmpty($params);

                return true;
            }

            public function getMessage(string $valueName, $value, array $params = []): string
            {
                return 'test';
            }
        };

        $validator = $this->createTestValidatorWithConstraint(
            [
                'test1' => 'test',
            ],
            $constraint
        );

        $validator->validate(['test1' => 5]);
    }

    public function testItPassesParamsIfContextIsProvided(): void
    {
        $constraint = new class implements ConstraintInterface {
            public static function name(): string
            {
                return 'test';
            }

            public function validate($value, array $params = [], array $allData = []): bool
            {
                Assert::assertEquals(['context'], $params);

                return true;
            }

            public function getMessage(string $valueName, $value, array $params = []): string
            {
                return 'test';
            }
        };

        $validator = $this->createTestValidatorWithConstraint(
            [
                'test1' => 'test:context',
            ],
            $constraint
        );

        $validator->validate(['test1' => 5]);
    }

    private function createTestValidator(array $rules): Validator
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

        return new Validator($constraintCollection, new RuleNormalizer(), $rules);
    }

    private function createTestValidatorWithConstraint(array $rules, ConstraintInterface $constraint): Validator
    {
        $constraintCollection = new ConstraintCollection();
        $constraintCollection->add($constraint);

        $arrayOf = new ArrayOfConstraint($constraintCollection);

        $constraintCollection->add($arrayOf);

        return new Validator($constraintCollection, new RuleNormalizer(), $rules);
    }
}
