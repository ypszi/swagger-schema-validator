<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Test\Unit\Constraint;

use PHPUnit\Framework\TestCase;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\InConstraint;

class InConstraintTest extends TestCase
{
    public function testItValidatesTheProvidedValue(): void
    {
        $constraint = new InConstraint();

        $this->assertTrue($constraint->validate('value', ['accepted', 'value']));
        $this->assertTrue($constraint->validate(null));
        $this->assertFalse($constraint->validate('value'));
        $this->assertFalse($constraint->validate('value', ['not', 'in', 'accepted', 'values']));
    }

    public function testItReturnsAMessage(): void
    {
        $constraint = new InConstraint();

        $this->assertEquals(
            'name should be one of (list, of, accepted, values) values.',
            $constraint->getMessage('name', 'value', ['list', 'of', 'accepted', 'values'])
        );
    }

    public function testIfNameIsCorrect(): void
    {
        $this->assertEquals('in', InConstraint::name());
    }
}
