<?php

declare(strict_types=1);

namespace Netmex\Lumina\Intent;

use Netmex\Lumina\Contracts\ArgumentBuilderDirectiveInterface;
use Netmex\Lumina\Contracts\DirectiveInterface;
use Netmex\Lumina\Contracts\FieldResolverInterface;
use Netmex\Lumina\Contracts\IntentMetaDataInterface;
use Netmex\Lumina\Directives\AbstractDirective;

final class Intent
{
    public string $typeName;

    public string $fieldName;

    public ?IntentMetaDataInterface $metaData = null;

    private ?Intent $parent = null;

    /** @var Intent[] */
    public array $children = [];

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

    public function addModifier(string $argName, DirectiveInterface $directive): void
    {
        $this->modifiers[$argName] = $directive;
    }

    public function addTypeModifier(string $typeName, AbstractDirective $directive): void
    {
        $this->typeModifiers[$typeName][] = $directive;
    }

    public function getTypeModifiers(): array
    {
        return $this->typeModifiers;
    }

    public function getChildByName(string $fieldName): ?self
    {
        foreach ($this->children as $child) {
            if ($child->fieldName === $fieldName) {
                return $child;
            }
        }

        return null;
    }

    public function getChildrenMetaData(): array
    {
        return array_filter(
            array_map(fn(Intent $child) => $child->metaData ?? null, $this->children),
            fn($meta) => $meta !== null
        );
    }


    public function setMetaData(IntentMetaData $param)
    {
        $this->metaData = $param;
    }

    public function getMetaData(): IntentMetaDataInterface
    {
        if ($this->metaData === null) {
            $this->metaData = new IntentMetaData();
        }

        return $this->metaData;
    }
}
