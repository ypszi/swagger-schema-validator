<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Test\Unit\Constraint;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\InstanceOfConstraint;

class InstanceOfConstraintTest extends TestCase
{
    public function testItValidatesTheProvidedValue(): void
    {
        $constraint = new InstanceOfConstraint();

        $this->assertTrue($constraint->validate(new DateTime(), [DateTime::class]));
        $this->assertTrue($constraint->validate(new DateTime(), [DateTimeInterface::class]));
        $this->assertTrue($constraint->validate(null, [DateTime::class]));
        $this->assertFalse($constraint->validate(new DateTime()));
        $this->assertFalse($constraint->validate(new DateTime(), [DateTimeImmutable::class]));
    }

    public function testItReturnsAMessage(): void
    {
        $constraint = new InstanceOfConstraint();

        $this->assertEquals(
            'name should be an instance of DateTime.',
            $constraint->getMessage('name', 'value', [DateTime::class])
        );
    }

    public function testIfNameIsCorrect(): void
    {
        $this->assertEquals('instanceOf', InstanceOfConstraint::name());
    }
}
