<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Test\Unit\Constraint;

use PHPUnit\Framework\TestCase;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\NotRequiredIfOneExistConstraint;

class NotRequiredIfOneExistConstraintTest extends TestCase
{
    public function testItValidatesTheProvidedValue()
    {
        $constraint = new NotRequiredIfOneExistConstraint();

        $this->assertTrue($constraint->validate(null, ['test'], ['test' => 'defined']));
        $this->assertTrue($constraint->validate(null, ['test', 'foo'], ['test' => 'defined']));
        $this->assertTrue($constraint->validate('defined value', ['test', 'foo'], ['test' => 'defined']));
        $this->assertTrue($constraint->validate('defined value', ['test']));
        $this->assertFalse($constraint->validate('defined value'));
        $this->assertFalse($constraint->validate(null));
        $this->assertFalse($constraint->validate(null, ['test'], ['test' => null]));
        $this->assertFalse($constraint->validate(null, ['test']));
    }

    public function testItReturnsAMessage()
    {
        $constraint = new NotRequiredIfOneExistConstraint();

        $this->assertEquals(
            "name is required because you didn't provide one of test.",
            $constraint->getMessage('name', 'value', ['test'])
        );
    }

    public function testIfNameIsCorrect()
    {
        $this->assertEquals('notRequiredIfOneExist', NotRequiredIfOneExistConstraint::name());
    }
}
