<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Test\Unit\Constraint;

use DateTime;
use PHPUnit\Framework\TestCase;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\StringConstraint;

class StringConstraintTest extends TestCase
{
    public function testItValidatesTheProvidedValueInNonStrictMode(): void
    {
        $constraint = new StringConstraint();

        $this->assertTrue($constraint->validate(5));
        $this->assertTrue($constraint->validate('5'));
        $this->assertTrue($constraint->validate('32.98'));
        $this->assertTrue($constraint->validate(5.5));
        $this->assertTrue($constraint->validate('string'));
        $this->assertTrue($constraint->validate(null));
        $this->assertFalse($constraint->validate(new DateTime()));
        $this->assertTrue($constraint->validate('Dani3432'));
        $this->assertTrue($constraint->validate('1111Dani'));
    }

    public function testItValidatesTheProvidedValueInStrictMode(): void
    {
        $constraint = new StringConstraint();

        $this->assertTrue($constraint->validate('string', ['strict']));
        $this->assertFalse($constraint->validate('5', ['strict']));
        $this->assertFalse($constraint->validate('32.98', ['strict']));
        $this->assertTrue($constraint->validate(null, ['strict']));
        $this->assertFalse($constraint->validate(5, ['strict']));
        $this->assertFalse($constraint->validate(5.5, ['strict']));
        $this->assertFalse($constraint->validate(new DateTime(), ['strict']));
        $this->assertTrue($constraint->validate('Dani3432', ['strict']));
        $this->assertTrue($constraint->validate('1111Dani', ['strict']));
    }

    public function testItReturnsAMessage(): void
    {
        $constraint = new StringConstraint();

        $this->assertEquals('name should be a string.', $constraint->getMessage('name', 'value'));
    }

    public function testIfNameIsCorrect(): void
    {
        $this->assertEquals('string', StringConstraint::name());
    }
}
