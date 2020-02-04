<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Test\Unit\Validator\Validator;

use PHPUnit\Framework\TestCase;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\ArrayOfConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\BooleanConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\ConstraintCollection;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\EmailConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\FloatConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\InstanceOfConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\IntegerConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\MaxConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\MinConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\RequiredConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\RequiredIfNotExistConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\StringConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Rule\RuleNormalizer;
use Ypszi\SwaggerSchemaValidator\Validator\Validator\ChainValidator;
use Ypszi\SwaggerSchemaValidator\Validator\Validator\Validator;

class ChainValidatorTest extends TestCase
{
    /** @var ChainValidator */
    private $chainValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->chainValidator = new ChainValidator();
    }

    public function testItSucceedChainValidationProcess(): void
    {
        $validator1 = $this->createTestValidator(
            [
                'test1' => 'required',
                'test2' => 'required|int',
                'test3' => 'string',
            ]
        );
        $validator2 = $this->createTestValidator(
            [
                'test4' => 'string',
                'test5' => 'int|requiredIfNotExist:test3,test4',
                'test6' => 'arrayOf:bool',
                'test7' => 'max:3',
            ]
        );

        $result = $this->chainValidator
            ->add($validator1)
            ->add($validator2)
            ->validate(
                [
                    'test1' => 'data',
                    'test2' => 5,
                    'test5' => 3,
                ]
            );

        $this->assertTrue($result->isValid());
        $this->assertEmpty($result->getErrors());
    }

    public function testItFailedWhenValidatingDataNotMatchingTheRules(): void
    {
        $validator1 = $this->createTestValidator(
            [
                'test1' => 'required',
                'test2' => 'int|max:10',
                'test3' => 'instanceOf:\DateTime',
            ]
        );
        $validator2 = $this->createTestValidator(
            [
                'test4' => 'arrayOf:int',
                'test5' => 'requiredIfNotExist:test3,test4',
            ]
        );

        $result = $this->chainValidator
            ->add($validator1)
            ->add($validator2)
            ->validate(
                [
                    'test2' => 'wrong int',
                    'test3' => 'not a DateTime',
                    'test4' => ['5', '6', 'string'],
                ]
            );

        $this->assertFalse($result->isValid());
        $this->assertEquals(
            [
                'test1' => ['test1 is required.'],
                'test2' => [
                    'test2 should be an integer.',
                    'test2 should be lower or equal to 10.',
                ],
                'test3' => ['test3 should be an instance of \DateTime.'],
                'test4' => ['test4 should be an array of int.'],
            ],
            $result->getErrors()
        );
    }

    private function createTestValidator(array $rules): Validator
    {
        $constraintCollection = new ConstraintCollection();
        $constraintCollection
            ->add(new RequiredConstraint())
            ->add(new StringConstraint())
            ->add(new IntegerConstraint())
            ->add(new MinConstraint())
            ->add(new FloatConstraint())
            ->add(new EmailConstraint())
            ->add(new InstanceofConstraint())
            ->add(new MaxConstraint())
            ->add(new RequiredIfNotExistConstraint())
            ->add(new BooleanConstraint());

        $arrayOf = new ArrayOfConstraint($constraintCollection);

        $constraintCollection->add($arrayOf);

        return new Validator($constraintCollection, new RuleNormalizer(), $rules);
    }
}
