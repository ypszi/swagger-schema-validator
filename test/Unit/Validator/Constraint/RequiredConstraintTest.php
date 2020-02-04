<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Test\Unit\Constraint;

use PHPUnit\Framework\TestCase;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\RequiredConstraint;

class RequiredConstraintTest extends TestCase
{
    public function testItValidatesTheProvidedValue(): void
    {
        $constraint = new RequiredConstraint();

        $this->assertTrue($constraint->validate('defined value'));
        $this->assertTrue($constraint->validate(0));
        $this->assertFalse($constraint->validate(null));
        $this->assertFalse($constraint->validate(''));
    }

    public function testItReturnsAMessage(): void
    {
        $constraint = new RequiredConstraint();

        $this->assertEquals('name is required.', $constraint->getMessage('name', 'value'));
    }

    public function testIfNameIsCorrect(): void
    {
        $this->assertEquals('required', RequiredConstraint::name());
    }
}
