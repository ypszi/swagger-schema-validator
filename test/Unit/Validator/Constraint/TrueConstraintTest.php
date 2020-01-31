<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Test\Unit\Constraint;

use PHPUnit\Framework\TestCase;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\TrueConstraint;

class TrueConstraintTest extends TestCase
{
    public function testItValidatesTheProvidedValue()
    {
        $constraint = new TrueConstraint();

        $this->assertTrue($constraint->validate(true));
        $this->assertTrue($constraint->validate('true'));
        $this->assertTrue($constraint->validate(1));
        $this->assertTrue($constraint->validate('1'));
        $this->assertTrue($constraint->validate(null));
        $this->assertFalse($constraint->validate(5));
        $this->assertFalse($constraint->validate('string'));
        $this->assertFalse($constraint->validate(false));
        $this->assertFalse($constraint->validate('false'));
        $this->assertFalse($constraint->validate(0));
        $this->assertFalse($constraint->validate('0'));
    }

    public function testItReturnsAMessage()
    {
        $constraint = new TrueConstraint();

        $this->assertEquals('name should be true.', $constraint->getMessage('name', 'value'));
    }

    public function testIfNameIsCorrect()
    {
        $this->assertEquals('true', TrueConstraint::name());
    }
}
