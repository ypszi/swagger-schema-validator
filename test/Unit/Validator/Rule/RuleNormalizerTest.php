<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Test\Unit\Rule;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Ypszi\SwaggerSchemaValidator\Validator\Rule\Rule;
use Ypszi\SwaggerSchemaValidator\Validator\Rule\RuleCollection;
use Ypszi\SwaggerSchemaValidator\Validator\Rule\RuleNormalizer;

class RuleNormalizerTest extends TestCase
{
    /** @var RuleNormalizer */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new RuleNormalizer();
    }

    public function testGetNormalizeRules(): void
    {
        $rules = $this->subject->getNormalizeRules(
            [
                'key1' => 'required',
                'key2' => 'required|int|min:5|max:15',
                'key3' => ['regexp:/[A,e](?:nt|pple)/'],
                'key4' => 'required|in:a,b,c',
                'key5' => 'arrayOf:int',
                'key6.*' => 'required|int',
                'key7' => 'required|arrayOf:int',
                'key7.*' => 'required|int',
                '*' => 'arrayOf:int',
                '*.*.*.email' => 'required|email',
            ]
        );

        $this->assertEquals(
            new RuleCollection(
                [
                    new Rule('key1', 'required'),
                    new Rule('key2', 'required'),
                    new Rule('key2', 'int'),
                    new Rule('key2', 'min', [5]),
                    new Rule('key2', 'max', [15]),
                    new Rule('key3', 'regexp', ['/[A,e](?:nt|pple)/']),
                    new Rule('key4', 'required'),
                    new Rule('key4', 'in', ['a', 'b', 'c']),
                    new Rule('key5', 'arrayOf', ['int']),
                    new Rule('key6.*', 'required'),
                    new Rule('key6.*', 'int'),
                    new Rule('key7', 'required'),
                    new Rule('key7', 'arrayOf', ['int']),
                    new Rule('key7.*', 'required'),
                    new Rule('key7.*', 'int'),
                    new Rule('*', 'arrayOf', ['int']),
                    new Rule('*.*.*.email', 'required'),
                    new Rule('*.*.*.email', 'email'),
                ]
            ),
            $rules
        );
    }

    /**
     * @param array $rules
     *
     * @dataProvider invalidRuleDataProvider
     */
    public function testSpecialRuleSeparatorsInRegexpAsStringRule(array $rules): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/^\w+ constraint cannot be used in string rules\.$/');

        $this->subject->getNormalizeRules($rules);
    }

    public function invalidRuleDataProvider(): array
    {
        return [
            'regexp as only rule' => [
                'rules' => [
                    'test1' => 'regexp:/[A,e](?:nt|pple)/',
                ],
            ],
            'regexp starting rule' => [
                'rules' => [
                    'test1' => 'regexp:/[A,e](?:nt|pple)/|maxLength:5',
                ],
            ],
            'regexp rule in the middle' => [
                'rules' => [
                    'test1' => 'minLength:3|regexp:/[A,e](?:nt|pple)/|maxLength:5',
                ],
            ],
            'regexp rule in the end' => [
                'rules' => [
                    'test1' => 'minLength:3|regexp:/[A,e](?:nt|pple)/',
                ],
            ],
        ];
    }
}
