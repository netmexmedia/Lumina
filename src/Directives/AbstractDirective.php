<?php

declare(strict_types=1);

namespace Netmex\Lumina\Directives;

use Doctrine\ORM\EntityManagerInterface;
use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\AST\EnumTypeDefinitionNode;
use GraphQL\Language\AST\EnumTypeExtensionNode;
use GraphQL\Language\AST\EnumValueDefinitionNode;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputObjectTypeExtensionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeExtensionNode;
use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\AST\ObjectTypeExtensionNode;
use GraphQL\Language\AST\ScalarTypeDefinitionNode;
use GraphQL\Language\AST\ScalarTypeExtensionNode;
use GraphQL\Language\AST\UnionTypeDefinitionNode;
use GraphQL\Language\AST\UnionTypeExtensionNode;
use Netmex\Lumina\Contracts\DirectiveInterface;

abstract class AbstractDirective implements DirectiveInterface
{
    public DirectiveNode $directiveNode;
    public ScalarTypeDefinitionNode|ScalarTypeExtensionNode|ObjectTypeDefinitionNode|ObjectTypeExtensionNode|InterfaceTypeDefinitionNode|InterfaceTypeExtensionNode|UnionTypeDefinitionNode|UnionTypeExtensionNode|EnumTypeDefinitionNode|EnumTypeExtensionNode|InputObjectTypeDefinitionNode|InputObjectTypeExtensionNode|FieldDefinitionNode|InputValueDefinitionNode|EnumValueDefinitionNode $definitionNode;
    protected array $arguments = [];

    private ?string $model;

    public function setModel(?string $model): void
    {
        $this->model = $model;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public static function name(): string
    {
        return static::class;
    }

    public function nodeName(): string
    {
        return $this->definitionNode->name->value;
    }

    public function modelClass(): ?string
    {
        $type = $this->definitionNode->type;

        while (!$type instanceof NamedTypeNode) {
            $type = $type->type;
        }

        return $type->name->value;
    }

    public function setArguments(array $arguments): void
    {
        $this->arguments = $arguments;
    }

    public function getArgument(string|int $index, mixed $default = null): mixed
    {
        return $this->arguments[$index] ?? $default;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    protected function getNamedTypeName($typeNode): string
    {
        while (!$typeNode instanceof NamedTypeNode) {
            $typeNode = $typeNode->type;
        }
        return $typeNode->name->value;
    }

    protected function resolveEntityFQCN(string $shortName, EntityManagerInterface $entityManager): ?string
    {
        foreach ($entityManager->getMetadataFactory()->getAllMetadata() as $meta) {
            if ($meta->getReflectionClass()->getShortName() === $shortName) {
                return $meta->getName();
            }
        }

        return null;
    }
}