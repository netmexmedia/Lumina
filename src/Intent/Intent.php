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

    private ?Intent $parent = null;

    /** @var Intent[] */
    private array $children = [];

    /** @var FieldResolverInterface|null */
    public ?FieldResolverInterface $resolver = null;

    /** @var array<string, ArgumentBuilderDirectiveInterface[]> */
    public array $modifiers = [];

    // Type-level directives/modifiers
    /** @var AbstractDirective[] */
    private array $typeModifiers = [];

    public function __construct(string $typeName, string $fieldName)
    {
        $this->typeName = $typeName;
        $this->fieldName = $fieldName;
    }

    public function addChild(Intent $intent): void
    {
        $this->children[] = $intent;
    }

    /** @return Intent[] */
    public function getChildren(): array
    {
        return $this->children;
    }

    public function setParent(Intent $parent): void
    {
        $this->parent = $parent;
    }

    public function getParent(): ?Intent
    {
        return $this->parent;
    }

    public function setResolver(FieldResolverInterface $directive): void
    {
        $this->resolver = $directive;
    }

    public function addModifier(string $argName, ArgumentBuilderDirectiveInterface $directive): void
    {
        $this->modifiers[$argName][] = $directive;
    }

    public function addTypeModifier(string $typeName, AbstractDirective $directive): void
    {
        $this->typeModifiers[$typeName][] = $directive;
    }

    public function getTypeModifiers(): array
    {
        return $this->typeModifiers;
    }
}
