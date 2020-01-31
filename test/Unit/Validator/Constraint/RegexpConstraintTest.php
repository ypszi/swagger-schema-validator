<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Test\Unit\Constraint;

use PHPUnit\Framework\TestCase;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\RegexpConstraint;

class RegexpConstraintTest extends TestCase
{
    /** @var RegexpConstraint */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new RegexpConstraint();
    }

    public function testName()
    {
        $this->assertEquals('regexp', RegexpConstraint::name());
    }

    public function testMessage()
    {
        $this->assertEquals(
            "test should match regular expression: /test/",
            $this->subject->getMessage('test', null, ['/test/'])
        );
    }

    public function testInvalidValues()
    {
        $this->assertFalse($this->subject->validate('test', ['/[0-9]+/']));
        $this->assertFalse($this->subject->validate('testX', ['/test$/']));
        $this->assertFalse($this->subject->validate('Xtest', ['/^test/']));
        $this->assertFalse($this->subject->validate('a', ['/a{2,3}/']));
        $this->assertFalse($this->subject->validate('phhp', ['/p.p/']));
        $this->assertFalse($this->subject->validate('10', ['/[a-zA-Z]+/']));
        $this->assertFalse($this->subject->validate('-1', ['/^[0-5]?[0-9]$/']));
        $this->assertFalse($this->subject->validate('60', ['/^[0-5]?[0-9]$/']));
        $this->assertFalse($this->subject->validate('aaa', ['/^.{2}$/']));
        $this->assertFalse($this->subject->validate('127.0.0.', ['/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\z/']));
    }

    public function testValidValues()
    {
        $this->assertTrue($this->subject->validate(null, ['/[a-z]+/']));
        $this->assertTrue($this->subject->validate('test', ['/[a-z]+/']));
        $this->assertTrue($this->subject->validate('test', ['/test$/']));
        $this->assertTrue($this->subject->validate('test', ['/^test/']));
        $this->assertTrue($this->subject->validate('aa', ['/a{2,3}/']));
        $this->assertTrue($this->subject->validate('aaa', ['/a{2,3}/']));
        $this->assertTrue($this->subject->validate('php', ['/p.p/']));
        $this->assertTrue($this->subject->validate('azAZbBcC', ['/[a-zA-Z]+/']));
        $this->assertTrue($this->subject->validate('0', ['/^[0-5]?[0-9]$/']));
        $this->assertTrue($this->subject->validate('00', ['/^[0-5]?[0-9]$/']));
        $this->assertTrue($this->subject->validate('59', ['/^[0-5]?[0-9]$/']));
        $this->assertTrue($this->subject->validate('aa', ['/^.{2}$/']));
        $this->assertTrue($this->subject->validate('127.0.0.1', ['/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\z/']));
    }
}
