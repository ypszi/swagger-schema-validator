<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Rule;

use Ypszi\SwaggerSchemaValidator\Validator\Core\AbstractStrictCollection;

/**
 * @method iterable|Rule[] getIterator
 */
class RuleCollection extends AbstractStrictCollection
{
    protected function canAdd($element): bool
    {
        return $element instanceof Rule;
    }

    protected function getElementType(): string
    {
        return Rule::class;
    }
}
