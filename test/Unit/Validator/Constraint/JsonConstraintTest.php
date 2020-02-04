<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Test\Unit\Constraint;

use PHPUnit\Framework\TestCase;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\JsonConstraint;

class JsonConstraintTest extends TestCase
{
    public function testItValidatesTheProvidedValue(): void
    {
        $constraint = new JsonConstraint();

        $this->assertTrue($constraint->validate(null));
        $this->assertTrue($constraint->validate("null"));
        $this->assertTrue($constraint->validate("false"));
        $this->assertTrue($constraint->validate("true"));
        $this->assertTrue($constraint->validate('{"a":"b"}'));

        $this->assertFalse($constraint->validate('invalid'));
        $this->assertFalse($constraint->validate('{"a"}'));
        $this->assertFalse($constraint->validate('{a:"b"}'));
        $this->assertFalse($constraint->validate('{"a":"b",}'));
        $this->assertFalse($constraint->validate("{'a':'b'}"));
    }

    public function testItReturnsAMessage(): void
    {
        $constraint = new JsonConstraint();

        $this->assertEquals('name should be a valid json string.', $constraint->getMessage('name', 'value'));
    }

    public function testIfNameIsCorrect(): void
    {
        $this->assertEquals('json', JsonConstraint::name());
    }
}
