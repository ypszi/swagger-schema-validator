<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Test\Unit\Constraint;

use PHPUnit\Framework\TestCase;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\NullConstraint;

class NullConstraintTest extends TestCase
{
    public function testItValidatesTheProvidedValue(): void
    {
        $constraint = new NullConstraint();

        $this->assertTrue($constraint->validate(null));
        $this->assertFalse($constraint->validate(false));
        $this->assertFalse($constraint->validate(0));
        $this->assertFalse($constraint->validate(''));
    }

    public function testItReturnsAMessage(): void
    {
        $constraint = new NullConstraint();

        $this->assertEquals('name should be null.', $constraint->getMessage('name', 'value'));
    }

    public function testIfNameIsCorrect(): void
    {
        $this->assertEquals('null', NullConstraint::name());
    }
}
