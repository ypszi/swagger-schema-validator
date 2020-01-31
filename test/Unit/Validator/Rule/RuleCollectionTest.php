<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Test\Unit\Rule;

use PHPUnit\Framework\TestCase;
use stdClass;
use Ypszi\SwaggerSchemaValidator\Validator\Rule\Rule;
use Ypszi\SwaggerSchemaValidator\Validator\Rule\RuleCollection;

class RuleCollectionTest extends TestCase
{
    public function testRulesCanBeAdded(): void
    {
        $subject = new RuleCollection();

        $rule = $this->createMock(Rule::class);

        $subject->add($rule);
        $subject->add($rule);
        $subject->add($rule);
        $subject->add($rule);

        $this->assertCount(4, $subject);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /^"[\\\w]+RuleCollection" should only contain "[\\\w]+Rule" elements$/
     *
     * @dataProvider invalidElementProvider
     */
    public function testAnythingElseThanRulesCannotBeAdded(): void
    {
        $subject = new RuleCollection();

        $rule = $this->createMock(stdClass::class);

        $subject->add($rule);
    }

    public function invalidElementProvider(): array
    {
        return [
            [$this->createMock(RuleCollection::class)],
            [new stdClass()],
            ['string'],
            [200],
            [65.53],
            [false],
        ];
    }
}
