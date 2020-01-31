<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Test\Unit\Constraint;

use PHPUnit\Framework\TestCase;
use stdClass;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\EmailConstraint;

class EmailConstraintTest extends TestCase
{
    /** @var EmailConstraint */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new EmailConstraint();
    }

    public function testName()
    {
        $this->assertEquals('email', EmailConstraint::name());
    }

    public function testMessage()
    {
        $this->assertEquals('test should be a valid email address.', $this->subject->getMessage('test', ''));
    }

    public function testInvalidValues()
    {
        $this->assertFalse($this->subject->validate('test'));
        $this->assertFalse($this->subject->validate(345));
        $this->assertFalse($this->subject->validate(true));
        $this->assertFalse($this->subject->validate(false));
        $this->assertFalse($this->subject->validate(new stdClass()));
        $this->assertFalse($this->subject->validate(new EmailConstraint()));
        $this->assertFalse($this->subject->validate([]));
        $this->assertFalse($this->subject->validate('@.hu'));
        $this->assertFalse($this->subject->validate('email@'));
        $this->assertFalse($this->subject->validate('email.com'));
        $this->assertFalse($this->subject->validate('अजय@डाटा.भारत'));
        $this->assertFalse($this->subject->validate('квіточка@пошта.укр'));
        $this->assertFalse($this->subject->validate('θσερ@εχαμπλε.ψομ'));
    }

    public function testValidValue()
    {
        $this->assertTrue($this->subject->validate('valid@email.com'));
    }
}
