<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Test\Unit\Constraint;

use PHPUnit\Framework\TestCase;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\UrlConstraint;

class UrlConstraintTest extends TestCase
{
    public function testItValidatesTheProvidedValue(): void
    {
        $constraint = new UrlConstraint();

        $this->assertTrue($constraint->validate(null));
        $this->assertTrue($constraint->validate('http://www.test.com'));
        $this->assertTrue($constraint->validate('https://www.test.com'));
        $this->assertFalse($constraint->validate('www.example.com'));
        $this->assertFalse($constraint->validate(5));
        $this->assertFalse($constraint->validate('string'));
        $this->assertFalse($constraint->validate(false));
    }

    public function testItReturnsAMessage(): void
    {
        $constraint = new UrlConstraint();

        $this->assertEquals("url must be a valid URL, 'value' provided.", $constraint->getMessage('url', 'value'));
    }

    public function testIfNameIsCorrect(): void
    {
        $this->assertEquals('url', UrlConstraint::name());
    }
}
