<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Test\Unit\Constraint;

use PHPUnit\Framework\TestCase;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\BooleanConstraint;

class BooleanConstraintTest extends TestCase
{
    public function testItValidatesTheProvidedValue(): void
    {
        $constraint = new BooleanConstraint();

        $this->assertTrue($constraint->validate(false));
        $this->assertTrue($constraint->validate('false'));
        $this->assertTrue($constraint->validate(0));
        $this->assertTrue($constraint->validate('0'));
        $this->assertTrue($constraint->validate(true));
        $this->assertTrue($constraint->validate('true'));
        $this->assertTrue($constraint->validate(1));
        $this->assertTrue($constraint->validate('1'));
        $this->assertTrue($constraint->validate(null));
        $this->assertFalse($constraint->validate(5));
        $this->assertFalse($constraint->validate('string'));
    }

    public function testItReturnsAMessage(): void
    {
        $constraint = new BooleanConstraint();

        $this->assertEquals('name should be a boolean.', $constraint->getMessage('name', 'value'));
    }

    public function testIfNameIsCorrect(): void
    {
        $this->assertEquals('bool', BooleanConstraint::name());
    }
}
