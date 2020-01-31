<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Swagger\ServiceProvider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Ypszi\SwaggerSchemaValidator\Swagger\Validator\SchemaValidator;
use Ypszi\SwaggerSchemaValidator\Validator\Constraint\ConstraintCollection;
use Ypszi\SwaggerSchemaValidator\Validator\Rule\RuleNormalizer;

class SwaggerServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container[SchemaValidator::class] = function (ContainerInterface $container): SchemaValidator {
            return new SchemaValidator(
                $container->get(ConstraintCollection::class),
                $container->get(RuleNormalizer::class)
            );
        };
    }
}
