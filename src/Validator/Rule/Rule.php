<?php declare(strict_types=1);

namespace Ypszi\SwaggerSchemaValidator\Validator\Rule;

class Rule
{
    /** @var string */
    private $key;

    /** @var string */
    private $name;

    /** @var array */
    private $context;

    public function __construct(string $key, string $name, array $context = [])
    {
        $this->key = $key;
        $this->name = $name;
        $this->context = $context;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
