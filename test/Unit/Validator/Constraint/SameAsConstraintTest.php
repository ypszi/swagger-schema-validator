<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Test\Unit\Constraint;

use PHPUnit\Framework\TestCase;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\SameAsConstraint;

class SameAsConstraintTest extends TestCase
{
    public function testItValidatesTheProvidedValue()
    {
        $constraint = new SameAsConstraint();

        $this->assertTrue($constraint->validate('goodPassword', ['password'], ['password' => 'goodPassword']));
        $this->assertTrue(
            $constraint->validate(
                'goodPassword',
                [
                    'password',
                    'passwordTest',
                ],
                [
                    'password' => 'goodPassword',
                    'passwordTest' => 'goodPassword',
                ]
            )
        );
        $this->assertTrue($constraint->validate(null));
        $this->assertFalse($constraint->validate('goodPassword', ['password'], ['password' => 'badPassword']));
        $this->assertFalse($constraint->validate('goodPassword', ['password'], []));
    }

    public function testItReturnsAMessage()
    {
        $constraint = new SameAsConstraint();

        $this->assertEquals(
            'The field password is not the same as passwordConfirmation.',
            $constraint->getMessage(
                'password',
                'badPassword',
                ['passwordConfirmation']
            )
        );
    }

    public function testIfNameIsCorrect()
    {
        $this->assertEquals('sameAs', SameAsConstraint::name());
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage The sameAs constraint is malformed. Expected: 'sameAs:fieldToCompare'.
     */
    public function testItWillThrowAnExceptionIfTheRuleIsMalformed()
    {
        $constraint = new SameAsConstraint();

        $constraint->validate('5');
    }
}
