<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Test\Unit\Constraint;

use PHPUnit\Framework\TestCase;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\ArrayOfConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\BooleanConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\ConstraintCollection;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\FloatConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\IntegerConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\StringConstraint;

class ArrayOfConstraintTest extends TestCase
{
    public function testItValidatesTheProvidedValue(): void
    {
        $constraints = new ConstraintCollection();
        $constraints->add(new IntegerConstraint());
        $constraints->add(new StringConstraint());
        $constraints->add(new BooleanConstraint());
        $constraints->add(new FloatConstraint());

        $constraint = new ArrayOfConstraint($constraints);

        $this->assertTrue($constraint->validate([1, 2, 3], [IntegerConstraint::name()]));
        $this->assertTrue($constraint->validate(null, [IntegerConstraint::name()]));
        $this->assertFalse($constraint->validate([1, 'string', 3], [IntegerConstraint::name()]));
        $this->assertFalse($constraint->validate([1, 2, 3]));
        $this->assertFalse($constraint->validate(null));
        $this->assertFalse($constraint->validate(true));
        $this->assertTrue($constraint->validate(['string', 'test'], [StringConstraint::name()]));
        $this->assertTrue($constraint->validate([1, 3, 3], [StringConstraint::name()]));
        $this->assertTrue($constraint->validate([true, false], [BooleanConstraint::name()]));
        $this->assertTrue($constraint->validate([7.01, 7.2], [FloatConstraint::name()]));
    }

    public function testItReturnsAMessage(): void
    {
        $constraint = new ArrayOfConstraint(new ConstraintCollection());

        $this->assertEquals(
            'name should be an array of int.',
            $constraint->getMessage('name', 'value', [IntegerConstraint::name()])
        );
    }

    public function testIfNameIsCorrect(): void
    {
        $this->assertEquals('arrayOf', ArrayOfConstraint::name());
    }
}
