<?php

declare(strict_types=1);

namespace Netmex\Lumina\Directives;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use Netmex\Lumina\Intent\Builder\IntentBuilder;

final class DirectiveContext
{
    private string $name;
    private object $node;
    private object $parentNode;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getNode(): object
    {
        return $this->node;
    }

    public function setNode(object $node): void
    {
        $this->node = $node;
    }

    public function getParentNode(): object
    {
        return $this->parentNode;
    }

    public function setParentNode(object $parentNode): void
    {
        $this->parentNode = $parentNode;
    }

    public function withNode($node): self
    {
        $clone = clone $this;

        $clone->setNode($node);
        return $clone;
    }

    public function withName(string $name): self
    {
        $clone = clone $this;

        $clone->setName($name);
        return $clone;
    }
}