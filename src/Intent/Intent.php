<?php

namespace Netmex\Lumina\Intent;

use Netmex\Lumina\Contracts\ArgumentBuilderDirectiveInterface;
use Netmex\Lumina\Contracts\FieldResolverInterface;

final class Intent
{
    public string $typeName;
    public string $fieldName;

    /** @var FieldResolverInterface|null */
    public ?FieldResolverInterface $resolverDirective = null;

    /** @var array<string, ArgumentBuilderDirectiveInterface[]> */
    public array $argumentDirectives = [];

    public function __construct(string $typeName, string $fieldName)
    {
        $this->typeName = $typeName;
        $this->fieldName = $fieldName;
    }

    public function addArgumentDirective(string $argName, ArgumentBuilderDirectiveInterface $directive): void
    {
        $this->argumentDirectives[$argName][] = $directive;
    }

    public function setResolver(FieldResolverInterface $directive): void
    {
        $this->resolverDirective = $directive;
    }
}
