<?php

declare(strict_types=1);

namespace Netmex\Lumina\Intent;

use Netmex\Lumina\Contracts\ArgumentBuilderDirectiveInterface;
use Netmex\Lumina\Contracts\FieldResolverInterface;
use Netmex\Lumina\Directives\AbstractDirective;

final class Intent
{
    public string $typeName;
    public string $fieldName;

    /** @var FieldResolverInterface|null */
    public ?FieldResolverInterface $resolverDirective = null;

    /** @var array<string, ArgumentBuilderDirectiveInterface[]> */
    public array $argumentDirectives = [];

    /** @var AbstractDirective[] Type-level directives applied to this intent */
    private array $typeDirectives = [];

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

    public function applyTypeDirective(string $typeName, AbstractDirective $directive): void
    {
        $this->typeDirectives[$typeName][] = $directive;
    }

    /** @return AbstractDirective[] All type-level directives for this field intent */
    public function getTypeDirectives(): array
    {
        return $this->typeDirectives;
    }
}
