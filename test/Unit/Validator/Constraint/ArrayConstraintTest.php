<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Test\Unit\Constraint;

use PHPUnit\Framework\TestCase;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\ArrayConstraint;

class ArrayConstraintTest extends TestCase
{
    public function testItValidatesTheProvidedValue()
    {
        $constraint = new ArrayConstraint();

        $this->assertTrue($constraint->validate([]));
        $this->assertTrue($constraint->validate(['key' => 'value']));
        $this->assertTrue($constraint->validate(null));

        $this->assertFalse($constraint->validate('true'));
        $this->assertFalse($constraint->validate(5.5));
        $this->assertFalse($constraint->validate(true));
        $this->assertFalse($constraint->validate(false));
        $this->assertFalse($constraint->validate(''));
    }

    public function testItReturnsAMessage()
    {
        $constraint = new ArrayConstraint();

        $this->assertEquals('name should be an array.', $constraint->getMessage('name', 'value'));
    }

    public function testIfNameIsCorrect()
    {
        $this->assertEquals('array', ArrayConstraint::name());
    }
}
