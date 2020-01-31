<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Test\Unit\Validator\Validator;

use PHPUnit\Framework\TestCase;
use Ypszi\SwaggerSchemaValidator\Validator\Validator\ValidationResult;

class ValidationResultTest extends TestCase
{
    public function testItReturnsIfTheResultIsValid()
    {
        $result = new ValidationResult([], []);

        $this->assertTrue($result->isValid());

        $result = new ValidationResult([], ['error']);

        $this->assertFalse($result->isValid());
    }

    public function testItReturnsTheErrors()
    {
        $result = new ValidationResult([], ['error']);

        $this->assertEquals(['error'], $result->getErrors());
    }

    public function testItReturnsTheValidatedData()
    {
        $result = new ValidationResult(['data'], []);

        $this->assertEquals(['data'], $result->getValidatedData());
    }
}
