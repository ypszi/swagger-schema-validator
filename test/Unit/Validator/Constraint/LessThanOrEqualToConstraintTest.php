<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Test\Unit\Constraint;

use DateTime;
use LogicException;
use PHPUnit\Framework\TestCase;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\LessThanOrEqualToConstraint;

class LessThanOrEqualToConstraintTest extends TestCase
{
    public function testItValidatesTheProvidedValue(): void
    {
        $constraint = new LessThanOrEqualToConstraint();

        $this->assertTrue($constraint->validate(-5, ['test', 'price'], ['test' => -4, 'price' => -3]));
        $this->assertTrue(
            $constraint->validate('2005-08-02T15:52:01+00:00', ['dateTo'], ['dateTo' => '2005-08-13T15:52:01+00:00'])
        );
        $this->assertTrue(
            $constraint->validate('2005-08-02T15:52:01+00:00', ['dateTo'], ['dateTo' => '2005-08-03T15:52:01+00:00'])
        );
        $this->assertTrue(
            $constraint->validate(
                new DateTime('2005-08-02T15:52:01+00:00'),
                ['dateTo'],
                ['dateTo' => new DateTime('2005-08-03T15:52:01+00:00')]
            )
        );
        $this->assertTrue(
            $constraint->validate(
                new DateTime('2005-08-02T15:52:01+00:00'),
                ['dateTo'],
                ['dateTo' => new DateTime('2005-08-02T15:52:01+00:00')]
            )
        );

        $this->assertTrue(
            $constraint->validate('2004-08-16T15:52:01+00:00', ['dateTo'], ['dateTo' => '2005-08-15T15:52:01+00:00'])
        );
        $this->assertTrue($constraint->validate(null));
        $this->assertTrue($constraint->validate(0, ['test'], ['test' => 0]));
        $this->assertTrue(
            $constraint->validate('2005-08-16T15:52:01+00:00', ['dateTo'], ['dateTo' => '2005-08-16T15:52:01+00:00'])
        );
        $this->assertTrue(
            $constraint->validate('2005-08-17T15:52:01+00:00', ['dateTo', 'date'], ['dateTo' => null, 'date' => null])
        );
        $this->assertTrue($constraint->validate('2005-08-17T15:52:01+00:00', ['dateTo']));
        $this->assertTrue(
            $constraint->validate(
                '2005-08-17T15:52:01+00:00',
                ['dateTo', 'date'],
                ['dateTo' => null, 'date' => '2005-08-17T15:52:01+00:00']
            )
        );
    }

    public function testItInvalidatesTheProvidedValue(): void
    {
        $constraint = new LessThanOrEqualToConstraint();

        $this->assertFalse($constraint->validate(5.0, ['number'], ['number' => 4]));
        $this->assertFalse(
            $constraint->validate('2005-09-15T15:52:01+00:00', ['dateTo'], ['dateTo' => '2005-08-15T15:52:01+00:00'])
        );
        $this->assertFalse(
            $constraint->validate('2006-08-16T15:52:01+00:00', ['dateTo'], ['dateTo' => '2005-08-15T15:52:01+00:00'])
        );
        $this->assertFalse($constraint->validate('3.5', ['price'], ['price' => 3]));
        $this->assertFalse($constraint->validate(-4, ['price'], ['price' => -5]));
        $this->assertFalse($constraint->validate(-2, ['test', 'price'], ['test' => -4, 'price' => -3]));
        $this->assertFalse(
            $constraint->validate(
                new DateTime('2005-08-04T15:52:01+00:00'),
                ['dateTo'],
                ['dateTo' => new DateTime('2005-08-03T15:52:01+00:00')]
            )
        );
        $this->assertFalse(
            $constraint->validate(
                '2005-08-17T15:52:01+00:00',
                ['dateTo', 'date'],
                ['dateTo' => null, 'date' => '2005-08-16T15:52:01+00:00']
            )
        );
    }

    public function testItWillThrowAnExceptionIfTheRuleIsMalformed(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            'The lessThanOrEqualTo constraint is malformed. Expected: \'lessThanOrEqualTo:fieldToCompare\'.'
        );
        $constraint = new LessThanOrEqualToConstraint();

        $constraint->validate('5');
    }

    public function testItReturnsAMessage(): void
    {
        $constraint = new LessThanOrEqualToConstraint();

        $this->assertEquals('5 should be less than or equal to 4.', $constraint->getMessage('5', '4', [4]));
    }

    public function testIfNameIsCorrect(): void
    {
        $this->assertEquals('lessThanOrEqualTo', LessThanOrEqualToConstraint::name());
    }
}
