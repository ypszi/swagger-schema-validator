<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Test\Unit\Constraint;

use DateTime;
use PHPUnit\Framework\TestCase;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\LessThanConstraint;

class LessThanConstraintTest extends TestCase
{
    public function testItValidatesTheProvidedValue()
    {
        $constraint = new LessThanConstraint();

        $this->assertTrue($constraint->validate(-5, ['test', 'price'], ['test' => -4, 'price' => -3]));
        $this->assertTrue(
            $constraint->validate('2005-08-02T15:52:01+00:00', ['dateTo'], ['dateTo' => '2005-08-13T15:52:01+00:00'])
        );
        $this->assertTrue(
            $constraint->validate('2005-08-02T15:52:01+00:00', ['dateTo'], ['dateTo' => '2005-08-03T15:52:01+00:00'])
        );
        $this->assertTrue(
            $constraint->validate('2005-08-17T15:52:01+00:00', ['dateTo', 'date'], ['dateTo' => null, 'date' => null])
        );
        $this->assertTrue($constraint->validate('2005-08-17T15:52:01+00:00', ['dateTo']));
        $this->assertTrue(
            $constraint->validate(
                '2005-08-16T15:52:01+00:00',
                ['dateTo', 'date'],
                ['dateTo' => null, 'date' => '2005-08-18T15:52:01+00:00']
            )
        );
        $this->assertTrue(
            $constraint->validate(
                new DateTime('2005-08-02T15:52:01+00:00'),
                ['dateTo'],
                ['dateTo' => new DateTime('2005-08-03T15:52:01+00:00')]
            )
        );
        $this->assertTrue($constraint->validate(null));
    }

    public function testItInvalidatesTheProvidedValue()
    {
        $constraint = new LessThanConstraint();

        $this->assertFalse($constraint->validate(0, ['test'], ['test' => 0]));
        $this->assertFalse($constraint->validate(5.0, ['number'], ['number' => 4]));
        $this->assertFalse(
            $constraint->validate('2005-09-15T15:52:01+00:00', ['dateTo'], ['dateTo' => '2005-08-15T15:52:01+00:00'])
        );
        $this->assertFalse(
            $constraint->validate('2005-08-16T15:52:01+00:00', ['dateTo'], ['dateTo' => '2005-08-15T15:52:01+00:00'])
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
                new DateTime('2005-08-02T15:52:01+00:00'),
                ['dateTo'],
                ['dateTo' => new DateTime('2005-08-02T15:52:01+00:00')]
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

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage The lessThan constraint is malformed. Expected: 'lessThan:fieldToCompare'.
     */
    public function testItWillThrowAnExceptionIfTheRuleIsMalformed()
    {
        $constraint = new LessThanConstraint();

        $constraint->validate('5');
    }

    public function testItReturnsAMessage()
    {
        $constraint = new LessThanConstraint();

        $this->assertEquals('5 should be less than 4.', $constraint->getMessage('5', '4', [4]));
    }

    public function testIfNameIsCorrect()
    {
        $this->assertEquals('lessThan', LessThanConstraint::name());
    }
}
