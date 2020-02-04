<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Test\Unit\Constraint;

use PHPUnit\Framework\TestCase;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\MinConstraint;

class MinConstraintTest extends TestCase
{
    public function testItValidatesTheProvidedValue(): void
    {
        $constraint = new MinConstraint();

        $this->assertTrue($constraint->validate(5.0, [5.0]));
        $this->assertTrue($constraint->validate(5, [4]));
        $this->assertTrue($constraint->validate('3.5', [3]));
        $this->assertTrue($constraint->validate(null));
        $this->assertTrue($constraint->validate(0, [0]));
        $this->assertTrue($constraint->validate(-4, [-5]));
        $this->assertFalse($constraint->validate(-6, [-5]));
        $this->assertFalse($constraint->validate(5, [6]));
    }

    public function testItReturnsAMessage(): void
    {
        $constraint = new MinConstraint();

        $this->assertEquals(
            'name should be greater or equal to 5.',
            $constraint->getMessage('name', 'value', [5])
        );
    }

    public function testIfNameIsCorrect(): void
    {
        $this->assertEquals('min', MinConstraint::name());
    }
}
