<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Test\Unit\Constraint;

use PHPUnit\Framework\TestCase;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\ConstraintCollection;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\RequiredConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\StringConstraint;
use Ypszi\SwaggerSchemaValidator\Validator\Exception\ConstraintCollectionException;

class ConstraintCollectionTest extends TestCase
{
    public function testItCanRetrieveAddedConstraints()
    {
        $constraint1 = new RequiredConstraint();
        $constraint2 = new StringConstraint();

        $collection = new ConstraintCollection();
        $collection
            ->add($constraint1)
            ->add($constraint2);

        $this->assertSame($constraint1, $collection->get(RequiredConstraint::name()));
        $this->assertSame($constraint2, $collection->get(StringConstraint::name()));
    }

    public function testItWillThrownAnExceptionIfWeTryToAddTwiceTheSameConstraint()
    {
        $this->expectException(ConstraintCollectionException::class);
        $this->expectExceptionMessageMatches('/^Constraint already added: "required"$/');

        $constraint1 = new RequiredConstraint();
        $constraint2 = new RequiredConstraint();

        $collection = new ConstraintCollection();
        $collection
            ->add($constraint1)
            ->add($constraint2);
    }

    public function testItThrowsAnExceptionIfAConstraintCannotBeFound()
    {
        $this->expectException(ConstraintCollectionException::class);
        $this->expectExceptionMessageMatches('/^Constraint not found: "required"$/');

        $collection = new ConstraintCollection();

        $collection->get(RequiredConstraint::name());
    }
}
