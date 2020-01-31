<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Test\Unit\Constraint;

use PHPUnit\Framework\TestCase;
use stdClass;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\ConstraintInterface;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\LengthConstraint;

class LengthConstraintTest extends TestCase
{
    /** @var LengthConstraint */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new LengthConstraint();
    }

    public function testItImplementsConstraintInterface(): void
    {
        $this->assertInstanceOf(ConstraintInterface::class, $this->subject);
    }

    public function testName(): void
    {
        $this->assertEquals('length', $this->subject::name());
    }

    public function testGetMessage(): void
    {
        $this->assertEquals(
            'value length should be equal to 2.',
            $this->subject->getMessage('value', '', [2])
        );
    }

    public function testItValidatesNullValue(): void
    {
        $this->assertTrue($this->subject->validate(null));
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage The 'length' constraint is malformed. Expected: 'length:{digitalNumber}'.
     */
    public function testItThrowsTheExceptionWhenMinValueIsNotProvided()
    {
        (new LengthConstraint())->validate('test string', []);
    }

    /**
     * @dataProvider             getWrongValidationDataTypes
     *
     * @expectedException \LogicException
     * @expectedExceptionMessage The 'length' constraint can be used to validate only strings or arrays length.
     */
    public function testItThrowsTheExceptionWhenValidatedValueIsNotStringOrArray($data)
    {
        (new LengthConstraint())->validate($data, [0]);
    }

    public function getWrongValidationDataTypes(): array
    {
        return [
            [
                1,
            ],
            [
                1.01,
            ],
            [
                true,
            ],
            [
                new stdClass(),
            ],
            [
                function () {
                },
            ],
        ];
    }

    /**
     * @dataProvider validDataProvider
     */
    public function testItValidatesLengthBothOfStringAndArray($value, bool $expectedResult): void
    {
        $this->assertEquals($expectedResult, $this->subject->validate($value, [2]));
    }

    public function testItValidatesLengthOfUnicodeString(): void
    {
        $this->assertTrue($this->subject->validate('😉', [1]));
    }

    public function validDataProvider(): array
    {
        return [
            'empty' => [
                '',
                false,
            ],
            '1 char' => [
                'h',
                false,
            ],
            '2 chars' => [
                'hu',
                true,
            ],
            '3 chars' => [
                'hun',
                false,
            ],
            'more than 3 chars' => [
                'hungary',
                false,
            ],
            'empty array' => [
                [],
                false,
            ],
            '1 element' => [
                [1],
                false,
            ],
            '2 elements' => [
                [1, 2],
                true,
            ],
            '3 elements' => [
                [1, 2, 3],
                false,
            ],
            'more than 3 elements' => [
                [1, 2, 3, 4, 5, 6, 7, 8, 9],
                false,
            ],
        ];
    }
}
