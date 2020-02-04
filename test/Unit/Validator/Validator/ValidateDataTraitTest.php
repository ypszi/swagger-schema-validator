<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Test\Unit\Validator\Validator;

use PHPUnit\Framework\TestCase;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\BooleanConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\ConstraintCollection;
use Ypszi\SwaggerSchemaValidator\Validator\Rule\RuleNormalizer;
use Ypszi\SwaggerSchemaValidator\Validator\Validator\ValidateDataTrait;
use Ypszi\SwaggerSchemaValidator\Validator\Validator\ValidationException;
use Ypszi\SwaggerSchemaValidator\Validator\Validator\Validator;

class ValidateDataTraitTest extends TestCase
{
    use ValidateDataTrait;

    public function testValidateMatchingTheRules(): void
    {
        $result = $this->getValidatedData($this->createTestValidator(['test1' => 'bool']), ['test1' => true]);

        $this->assertEquals(['test1' => true], $result);
    }

    public function testValidateDataNotMatchingTheRules(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Validation error');

        $result = $this->getValidatedData($this->createTestValidator(['test1' => 'bool']), ['test1' => 989]);

        $this->assertEquals(['test1' => 989], $result);
    }

    private function createTestValidator(array $rules): Validator
    {
        $constraints = new ConstraintCollection();
        $constraints->add(new BooleanConstraint());

        return new Validator($constraints, new RuleNormalizer(), $rules);
    }
}
