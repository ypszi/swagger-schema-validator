<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Test\Unit\Constraint;

use PHPUnit\Framework\TestCase;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\RequiredIfExistConstraint;

class RequiredIfExistConstraintTest extends TestCase
{
    public function testItValidatesTheProvidedValue()
    {
        $constraint = new RequiredIfExistConstraint();

        $this->assertTrue($constraint->validate('defined value'));
        $this->assertTrue($constraint->validate(null));
        $this->assertTrue($constraint->validate(null, ['test'], ['test' => null]));
        $this->assertTrue($constraint->validate(null, ['test']));
        $this->assertFalse($constraint->validate(null, ['test'], ['test' => 'defined']));
    }

    public function testItReturnsAMessage()
    {
        $constraint = new RequiredIfExistConstraint();

        $this->assertEquals(
            "name is required because you provided test.",
            $constraint->getMessage('name', 'value', ['test'])
        );
    }

    public function testIfNameIsCorrect()
    {
        $this->assertEquals('requiredIfExist', RequiredIfExistConstraint::name());
    }
}
