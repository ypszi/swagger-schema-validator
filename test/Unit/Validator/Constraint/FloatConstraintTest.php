<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Test\Unit\Constraint;

use PHPUnit\Framework\TestCase;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\FloatConstraint;

class FloatConstraintTest extends TestCase
{
    public function testItValidatesTheProvidedValue(): void
    {
        $constraint = new FloatConstraint();

        $this->assertTrue($constraint->validate(5.0));
        $this->assertTrue($constraint->validate(5));
        $this->assertTrue($constraint->validate('3.5'));
        $this->assertTrue($constraint->validate(null));
        $this->assertTrue($constraint->validate(1e7));
        $this->assertFalse($constraint->validate(true));
        $this->assertFalse($constraint->validate('2.1 not a float'));
    }

    public function testItReturnsAMessage(): void
    {
        $constraint = new FloatConstraint();

        $this->assertEquals('name should be a float.', $constraint->getMessage('name', 'value'));
    }

    public function testIfNameIsCorrect(): void
    {
        $this->assertEquals('float', FloatConstraint::name());
    }
}
