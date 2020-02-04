<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Test\Unit\Constraint;

use PHPUnit\Framework\TestCase;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\RequiredIfNotExistConstraint;

class RequiredIfNotExistConstraintTest extends TestCase
{
    public function testItValidatesTheProvidedValue(): void
    {
        $constraint = new RequiredIfNotExistConstraint();

        $this->assertTrue($constraint->validate('defined value'));
        $this->assertTrue($constraint->validate(null, ['test'], ['test' => 'defined']));
        $this->assertFalse($constraint->validate(null));
        $this->assertFalse($constraint->validate(null, ['test'], ['test' => null]));
        $this->assertFalse($constraint->validate(null, ['test']));
        $this->assertTrue($constraint->validate(null, ['test.key1'], ['test' => ['key1' => 'defined']]));
        $this->assertFalse($constraint->validate(null, ['test.key1.key2'], ['test' => ['key1' => 'defined']]));
    }

    public function testItReturnsAMessage(): void
    {
        $constraint = new RequiredIfNotExistConstraint();

        $this->assertEquals(
            "name is required because you didn't provide test.",
            $constraint->getMessage('name', 'value', ['test'])
        );
    }

    public function testIfNameIsCorrect(): void
    {
        $this->assertEquals('requiredIfNotExist', RequiredIfNotExistConstraint::name());
    }
}
