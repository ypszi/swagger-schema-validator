<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Test\Unit\Constraint;

use DateTime;
use PHPUnit\Framework\TestCase;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\DateTimeStringConstraint;

class DateTimeStringConstraintTest extends TestCase
{
    public function testItValidatesTheProvidedValue()
    {
        $constraint = new DateTimeStringConstraint();

        $this->assertTrue($constraint->validate('2005-08-15T15:52:01+00:00'));
        $this->assertTrue($constraint->validate('2005-08-15T15:52:01+0000', [DateTime::ISO8601]));
        $this->assertTrue($constraint->validate(null));
        $this->assertFalse($constraint->validate('2005-08-15T15:52:01'));
        $this->assertFalse($constraint->validate('2005-08-15'));
        $this->assertFalse($constraint->validate(new DateTime()));
        $this->assertFalse($constraint->validate(true));
    }

    public function testItReturnsAMessage()
    {
        $constraint = new DateTimeStringConstraint();

        $this->assertEquals(
            'name should be a valid datetime in "Y-m-d\TH:i:sP" format.',
            $constraint->getMessage('name', 'value')
        );
    }

    public function testIfNameIsCorrect()
    {
        $this->assertEquals('dateTimeString', DateTimeStringConstraint::name());
    }
}
