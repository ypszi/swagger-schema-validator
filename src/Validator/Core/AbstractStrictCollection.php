<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Core;

use Doctrine\Common\Collections\ArrayCollection;
use InvalidArgumentException;

abstract class AbstractStrictCollection extends ArrayCollection
{
    protected function assertType($element): void
    {
        if (!$this->canAdd($element)) {
            throw new InvalidArgumentException(
                sprintf('"%s" should only contain "%s" elements', static::class, $this->getElementType())
            );
        }
    }

    abstract protected function canAdd($element): bool;

    abstract protected function getElementType(): string;

    public function add($value)
    {
        $this->assertType($value);

        parent::add($value);
    }
}
