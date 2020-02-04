<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Test\Unit\Constraint;

use DateTime;
use LogicException;
use PHPUnit\Framework\TestCase;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\GreaterThanConstraint;

class GreaterThanConstraintTest extends TestCase
{
    public function testItValidatesTheProvidedValue(): void
    {
        $constraint = new GreaterThanConstraint();

        $this->assertTrue($constraint->validate(5.0, ['number'], ['number' => 4]));
        $this->assertTrue(
            $constraint->validate('2005-09-15T15:52:01+00:00', ['dateTo'], ['dateTo' => '2005-08-15T15:52:01+00:00'])
        );
        $this->assertTrue(
            $constraint->validate('2005-08-16T15:52:01+00:00', ['dateTo'], ['dateTo' => '2005-08-15T15:52:01+00:00'])
        );
        $this->assertTrue($constraint->validate('3.5', ['price'], ['price' => 3]));
        $this->assertTrue($constraint->validate(null));
        $this->assertTrue($constraint->validate(-4, ['price'], ['price' => -5]));
        $this->assertTrue($constraint->validate(-2, ['test', 'price'], ['test' => -4, 'price' => -3]));
        $this->assertTrue($constraint->validate(-2, ['test'], ['test' => null]));
        $this->assertTrue(
            $constraint->validate('2005-08-17T15:52:01+00:00', ['dateTo', 'date'], ['dateTo' => null, 'date' => null])
        );
        $this->assertTrue($constraint->validate('2005-08-17T15:52:01+00:00', ['dateTo']));
        $this->assertTrue(
            $constraint->validate(
                '2005-08-17T15:52:01+00:00',
                ['dateTo', 'date'],
                ['dateTo' => null, 'date' => '2005-08-16T15:52:01+00:00']
            )
        );
        $this->assertTrue(
            $constraint->validate(
                new DateTime('2005-08-06T15:52:01+00:00'),
                ['dateTo'],
                ['dateTo' => new DateTime('2005-08-05T15:52:01+00:00')]
            )
        );
    }

    public function testItInvalidatesTheProvidedValues(): void
    {
        $constraint = new GreaterThanConstraint();

        $this->assertFalse($constraint->validate(0, ['test'], ['test' => 0]));
        $this->assertFalse(
            $constraint->validate('2005-08-02T15:52:01+00:00', ['dateTo'], ['dateTo' => '2005-08-02T15:52:01+00:00'])
        );
        $this->assertFalse($constraint->validate(-5, ['test', 'price'], ['test' => -4, 'price' => -3]));
        $this->assertFalse(
            $constraint->validate('2005-08-02T15:52:01+00:00', ['dateTo'], ['dateTo' => '2005-08-13T15:52:01+00:00'])
        );
        $this->assertFalse(
            $constraint->validate(
                new DateTime('2005-08-04T15:52:01+00:00'),
                ['dateTo'],
                ['dateTo' => new DateTime('2005-08-05T15:52:01+00:00')]
            )
        );
        $this->assertFalse(
            $constraint->validate(
                new DateTime('2005-08-02T15:52:01+00:00'),
                ['dateTo'],
                ['dateTo' => new DateTime('2005-08-02T15:52:01+00:00')]
            )
        );
        $this->assertFalse(
            $constraint->validate(
                '2005-08-15T15:52:01+00:00',
                ['dateTo', 'date'],
                ['dateTo' => null, 'date' => '2005-08-16T15:52:01+00:00']
            )
        );
    }

    public function testItWillThrowAnExceptionIfTheRuleIsMalformed(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            'The greaterThan constraint is malformed. Expected: \'greaterThan:fieldToCompare\'.'
        );

        $constraint = new GreaterThanConstraint();

        $constraint->validate('5');
    }

    public function testItReturnsAMessage(): void
    {
        $constraint = new GreaterThanConstraint();

        $this->assertEquals('4 should be greater than 5.', $constraint->getMessage('4', '7', [5]));
    }

    public function testIfNameIsCorrect(): void
    {
        $this->assertEquals('greaterThan', GreaterThanConstraint::name());
    }
}
