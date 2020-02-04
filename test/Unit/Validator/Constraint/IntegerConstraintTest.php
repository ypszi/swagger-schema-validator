<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Test\Unit\Constraint;

use PHPUnit\Framework\TestCase;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\IntegerConstraint;

class IntegerConstraintTest extends TestCase
{
    public function testItValidatesTheProvidedValue(): void
    {
        $constraint = new IntegerConstraint();

        $this->assertTrue($constraint->validate(5));
        $this->assertTrue($constraint->validate('5'));
        $this->assertTrue($constraint->validate(1147483647));
        $this->assertTrue($constraint->validate(0x539));
        $this->assertTrue($constraint->validate(1337e0));
        $this->assertTrue($constraint->validate(0b10100111001));
        $this->assertTrue($constraint->validate(null));
        $this->assertFalse($constraint->validate('true'));
        $this->assertTrue($constraint->validate('-0'));
        $this->assertFalse($constraint->validate(5.5));
        $this->assertFalse($constraint->validate(true));
        $this->assertFalse($constraint->validate(false));
        $this->assertFalse($constraint->validate('2 not an integer'));
        $this->assertFalse($constraint->validate(''));
    }

    public function testItReturnsAMessage(): void
    {
        $constraint = new IntegerConstraint();

        $this->assertEquals('name should be an integer.', $constraint->getMessage('name', 'value'));
    }

    public function testIfNameIsCorrect(): void
    {
        $this->assertEquals('int', IntegerConstraint::name());
    }
}
