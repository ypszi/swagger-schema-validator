<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Test\Unit\Constraint;

use PHPUnit\Framework\TestCase;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\MaxConstraint;

class MaxConstraintTest extends TestCase
{
    public function testItValidatesTheProvidedValue()
    {
        $constraint = new MaxConstraint();

        $this->assertTrue($constraint->validate(5.0, [5.0]));
        $this->assertTrue($constraint->validate(5, [6]));
        $this->assertTrue($constraint->validate('3.5', [4]));
        $this->assertFalse($constraint->validate('3.5', [3]));
        $this->assertTrue($constraint->validate(null));
        $this->assertTrue($constraint->validate(0, [0]));
        $this->assertTrue($constraint->validate(-6, [-5]));
        $this->assertFalse($constraint->validate(-4, [-5]));
        $this->assertFalse($constraint->validate(5, [4]));
    }

    public function testItReturnsAMessage()
    {
        $constraint = new MaxConstraint();

        $this->assertEquals(
            'name should be lower or equal to 5.',
            $constraint->getMessage('name', 'value', [5])
        );
    }

    public function testIfNameIsCorrect()
    {
        $this->assertEquals('max', MaxConstraint::name());
    }
}
