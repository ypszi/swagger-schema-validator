<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Test\Unit\Constraint;

use PHPUnit\Framework\TestCase;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\FalseConstraint;

class FalseConstraintTest extends TestCase
{
    public function testItValidatesTheProvidedValue()
    {
        $constraint = new FalseConstraint();

        $this->assertTrue($constraint->validate(false));
        $this->assertTrue($constraint->validate('false'));
        $this->assertTrue($constraint->validate(0));
        $this->assertTrue($constraint->validate('0'));
        $this->assertTrue($constraint->validate(null));
        $this->assertFalse($constraint->validate(5));
        $this->assertFalse($constraint->validate('string'));
        $this->assertFalse($constraint->validate(true));
        $this->assertFalse($constraint->validate('true'));
        $this->assertFalse($constraint->validate(1));
        $this->assertFalse($constraint->validate('1'));
    }

    public function testItReturnsAMessage()
    {
        $constraint = new FalseConstraint();

        $this->assertEquals('name should be false.', $constraint->getMessage('name', 'value'));
    }

    public function testIfNameIsCorrect()
    {
        $this->assertEquals('false', FalseConstraint::name());
    }
}
