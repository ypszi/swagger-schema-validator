<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Test\Unit\Constraint;

use PHPUnit\Framework\TestCase;
use stdClass;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\MinLengthConstraint;

class MinLengthConstraintTest extends TestCase
{
    public function testItValidatesTheProvidedValue()
    {
        $constraint = new MinLengthConstraint();

        $this->assertTrue($constraint->validate('test string', [0]));
        $this->assertTrue($constraint->validate('test string', [11]));
        $this->assertTrue($constraint->validate('', [-10]));
        $this->assertFalse($constraint->validate('test string', [12]));
        $this->assertFalse($constraint->validate('', [1]));
        $this->assertTrue($constraint->validate([1, 2], [2]));
        $this->assertTrue($constraint->validate([1, 2, 3], [2]));
        $this->assertTrue($constraint->validate([], [0]));
        $this->assertTrue($constraint->validate([], [-15]));
        $this->assertFalse($constraint->validate([], [1]));
        $this->assertFalse($constraint->validate([1, 2, 3], [4]));
        $this->assertTrue($constraint->validate(null, [5]));
        $this->assertTrue($constraint->validate(null));
        $this->assertTrue($constraint->validate(1, [1]));
        $this->assertFalse($constraint->validate(new stdClass(), [1]));
        $this->assertTrue($constraint->validate(1.01, [1]));
        $this->assertFalse(
            $constraint->validate(
                function () {
                },
                [1]
            )
        );
        $this->assertTrue($constraint->validate('说/説说/説说/説说/説说', [13]));
        $this->assertTrue($constraint->validate('echo', [1]));
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage The 'minLength' constraint is malformed. Expected: 'minLength:{digitalNumber}'.
     */
    public function testItThrowsTheExceptionWhenMinValueIsNotProvided()
    {
        (new MinLengthConstraint())->validate('test string', []);
    }

    public function testItReturnsAMessage()
    {
        $constraint = new MinLengthConstraint();

        $this->assertEquals(
            'name length should be greater than or equal to 5.',
            $constraint->getMessage('name', 'value', [5])
        );
    }

    public function testIfNameIsCorrect()
    {
        $this->assertEquals('minLength', MinLengthConstraint::name());
    }
}
