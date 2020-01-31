<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Test\Unit\Constraint;

use PHPUnit\Framework\TestCase;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\Base64Constraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\ConstraintInterface;

class Base64ConstraintTest extends TestCase
{
    /** @var Base64Constraint */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Base64Constraint();
    }


    public function testItImplementsConstraintInterface(): void
    {
        $this->assertInstanceOf(ConstraintInterface::class, $this->subject);
    }

    public function testName(): void
    {
        $this->assertEquals('base64', $this->subject::name());
    }

    public function testItValidatesNullValue(): void
    {
        $this->assertTrue($this->subject->validate(null));
    }

    public function testItValidatesValidEmptyValue(): void
    {
        $this->assertTrue($this->subject->validate(''));
    }

    /**
     * @dataProvider validBase64StringProvider
     */
    public function testItValidateBase64String($value): void
    {
        $this->assertTrue($this->subject->validate($value));
    }

    public function validBase64StringProvider(): array
    {
        return [
            ['YXBwbGU'],
            ['YXBwbGU='],
            [
                'dGhpcyBpcy
BzYW5mb3Vu
ZHJ5IGxpbn
V4IHR1dG9y
aWFsCg==',
            ],
            ['iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mPsl+6vBwAENAG6+oQbCwAAAABJRU5ErkJggg=='],
        ];
    }

    public function testItDoesNotValidateMalformedBase64String(): void
    {
        // the correct format is YXBwbGU=
        $this->assertFalse($this->subject->validate('YXBwbGU=='));
    }

    /**
     * @dataProvider invalidBase64StringProvider
     */
    public function testDoesNotValidateInvalidBase64String($valueName, $value): void
    {
        $this->assertFalse($this->subject->validate($value));
        $this->assertEquals(
            sprintf('%s is not base64 encoded properly.', $valueName),
            $this->subject->getMessage($valueName, $value)
        );
    }

    public function invalidBase64StringProvider(): array
    {
        return [
            [
                'test1',
                '  X ',
            ],
            [
                'test2',
                'dsadsa%',
            ],
            [
                'test3',
                '=123=',
            ],
        ];
    }
}
