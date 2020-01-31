<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Test\Unit\Constraint;

use PHPUnit\Framework\TestCase;
use stdClass;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\MaxLengthConstraint;

class MaxLengthConstraintTest extends TestCase
{
    public function testItValidatesTheProvidedValue()
    {
        $constraint = new MaxLengthConstraint();

        $this->assertTrue($constraint->validate('test string', [12]));
        $this->assertTrue($constraint->validate('test string', [11]));
        $this->assertTrue($constraint->validate('', [1]));
        $this->assertFalse($constraint->validate('', [-10]));
        $this->assertFalse($constraint->validate('test string', [10]));
        $this->assertTrue($constraint->validate([1, 2], [2]));
        $this->assertTrue($constraint->validate([1, 2, 3], [4]));
        $this->assertTrue($constraint->validate([], [1]));
        $this->assertTrue($constraint->validate([], [0]));
        $this->assertFalse($constraint->validate([], [-15]));
        $this->assertFalse($constraint->validate([1, 2, 3], [2]));
        $this->assertTrue($constraint->validate(null, [5]));
        $this->assertTrue($constraint->validate(null));
        $this->assertTrue($constraint->validate(1, [1]));
        $this->assertFalse($constraint->validate(new stdClass(), [1]));
        $this->assertFalse($constraint->validate(1.01, [1]));
        $this->assertFalse(
            $constraint->validate(
                function () {
                },
                [1]
            )
        );
        $this->assertTrue($constraint->validate('说/説说/説说/説说/説说', [13]));
        $this->assertTrue($constraint->validate('array_merge', [20]));
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage The 'maxLength' constraint is malformed. Expected: 'maxLength:{digitalNumber}'.
     */
    public function testItThrowsTheExceptionWhenMinValueIsNotProvided()
    {
        (new MaxLengthConstraint())->validate('test string', []);
    }

    public function testItReturnsAMessage()
    {
        $constraint = new MaxLengthConstraint();

        $this->assertEquals(
            'name length should be less than or equal to 5.',
            $constraint->getMessage('name', 'value', [5])
        );
    }

    public function testIfNameIsCorrect()
    {
        $this->assertEquals('maxLength', MaxLengthConstraint::name());
    }
}
