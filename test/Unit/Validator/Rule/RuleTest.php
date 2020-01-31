<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Test\Unit\Rule;

use PHPUnit\Framework\TestCase;
use Ypszi\SwaggerSchemaValidator\Validator\Rule\Rule;

class RuleTest extends TestCase
{
    public function testGetter(): void
    {
        $subject = new Rule('key', 'name', ['context']);

        $this->assertEquals('key', $subject->getKey());
        $this->assertEquals('name', $subject->getName());
        $this->assertEquals(['context'], $subject->getContext());
    }
}
