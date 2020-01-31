<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Test\Unit\Constraint;

use PHPUnit\Framework\TestCase;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\IpAddressConstraint;

class IpAddressConstraintTest extends TestCase
{
    /** @var IpAddressConstraint */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new IpAddressConstraint();
    }

    public function testName()
    {
        $this->assertEquals('ipAddress', IpAddressConstraint::name());
    }

    public function testMessage()
    {
        $this->assertEquals(
            "'test' should be a valid ip address.",
            $this->subject->getMessage('test', '')
        );
    }

    public function testInvalidValues()
    {
        $this->assertFalse($this->subject->validate('test'));
        $this->assertFalse($this->subject->validate('127'));
        $this->assertFalse($this->subject->validate('127.0'));
        $this->assertFalse($this->subject->validate('127.0.0'));
        $this->assertFalse($this->subject->validate('127.0.0.1000'));
        $this->assertFalse($this->subject->validate('256.256.256.256'));
    }

    public function testValidValues()
    {
        $this->assertTrue($this->subject->validate(null));
        $this->assertTrue($this->subject->validate('127.0.0.1'));
        $this->assertTrue($this->subject->validate('0.0.0.0'));
        $this->assertTrue($this->subject->validate('255.255.255.255'));
        $this->assertTrue($this->subject->validate('2001:0db8:0a0b:12f0:0000:0000:0000:0001'));
        $this->assertTrue($this->subject->validate('2001:0db8:0a0b:12f0:0000:0000:0000:0001'));
    }
}
