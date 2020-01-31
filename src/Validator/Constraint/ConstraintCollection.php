<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Constraint;

use Ypszi\SwaggerSchemaValidator\Validator\Exception\ConstraintCollectionException;

class ConstraintCollection
{
    /** @var ConstraintInterface[] */
    private $constraints;

    public function add(ConstraintInterface $constraint): self
    {
        $constraintName = $constraint::name();

        if (isset($this->constraints[$constraintName])) {
            throw new ConstraintCollectionException(sprintf('Constraint already added: "%s"', $constraintName));
        }

        $this->constraints[$constraintName] = $constraint;

        return $this;
    }

    public function get(string $constraintName): ConstraintInterface
    {
        if (!isset($this->constraints[$constraintName])) {
            throw new ConstraintCollectionException(sprintf('Constraint not found: "%s"', $constraintName));
        }

        return $this->constraints[$constraintName];
    }
}
