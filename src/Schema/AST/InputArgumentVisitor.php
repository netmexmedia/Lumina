<?php

namespace Netmex\Lumina\Schema\AST;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use Netmex\Lumina\Contracts\ArgumentBuilderDirectiveInterface;
use Netmex\Lumina\Contracts\FieldArgumentDirectiveInterface;
use Netmex\Lumina\Contracts\FieldResolverInterface;
use Netmex\Lumina\Directives\AbstractDirective;
use Netmex\Lumina\Intent\Intent;
use Netmex\Lumina\Directives\Registry\DirectiveRegistry;
use Symfony\Component\DependencyInjection\ServiceLocator;

final class InputArgumentVisitor
{
    private array $inputTypes = [];

    public function __construct(
        private readonly DirectiveRegistry $directiveRegistry,
        private readonly ServiceLocator $directiveLocator
    ) {}

    public function visitInputArgument(Intent $intent, InputValueDefinitionNode $argNode, array $inputTypes, string $parentPath = ''): void
    {
        $this->inputTypes = $inputTypes;

        $argPath = $this->buildArgumentPath($argNode, $parentPath);
        $this->applyArgumentNodeDirectives($intent, $argNode, $argPath);

        $namedType = $this->getNamedType($argNode->type);
        if (isset($inputTypes[$namedType])) {
            foreach ($inputTypes[$namedType]->fields as $nestedArg) {
                $this->visitInputArgument($intent, $nestedArg, $inputTypes, $argPath);
            }
        }
    }

    // Traverse nested return type fields for directives like @hasMany
    public function traverseReturnTypeFields(Intent $intent, string $typeName, string $prefix = ''): void
    {
        $typeDef = $this->getObjectTypeDefinition($typeName);
        if (!$typeDef) return;

        foreach ($typeDef->fields as $fieldNode) {
            $fieldPath = $prefix === '' ? $fieldNode->name->value : $prefix . '.' . $fieldNode->name->value;

            foreach ($fieldNode->directives as $directiveNode) {
                $directive = $this->instantiateDirective($directiveNode->name->value, $fieldNode, $directiveNode);
                if ($directive instanceof ArgumentBuilderDirectiveInterface) {
                    $intent->addArgumentDirective($fieldPath, $directive);
                }
            }

            $nestedType = $this->getNamedType($fieldNode->type);
            $this->traverseReturnTypeFields($intent, $nestedType, $fieldPath, $document);
        }
    }

    // Resolve a named type from any AST node
    public function getNamedType($typeNode): string
    {
        if (property_exists($typeNode, 'name') && $typeNode->name !== null) {
            return $typeNode->name->value;
        }
        if (property_exists($typeNode, 'type') && $typeNode->type !== null) {
            return $this->getNamedType($typeNode->type);
        }
        throw new \RuntimeException('Cannot resolve named type from AST node');
    }

    private function buildArgumentPath(InputValueDefinitionNode $argNode, string $parentPath): string
    {
        return $parentPath === '' ? $argNode->name->value : $parentPath . '.' . $argNode->name->value;
    }

    private function applyArgumentNodeDirectives(Intent $intent, InputValueDefinitionNode $argNode, string $argPath): void
    {
        foreach ($argNode->directives as $directiveNode) {
            $directive = $this->instantiateDirective($directiveNode->name->value, $argNode, $directiveNode);

            if ($directive instanceof ArgumentBuilderDirectiveInterface) {
                $intent->addArgumentDirective($argPath, $directive);
            }
        }
    }

    public function collectTypeDirectives($typeNode): array
    {
        $directives = [];
        foreach ($typeNode->directives as $directiveNode) {
            $directives[] = $this->instantiateDirective($directiveNode->name->value, $typeNode, $directiveNode);
        }
        return $directives;
    }

    public function applyTypeDirectivesToIntent($intent, array $typeDirectives): void
    {
        foreach ($typeDirectives as $directive) {
            $intent->applyTypeDirective($directive->name(), $directive);
        }
    }

    public function applyFieldDirectives(Intent $intent, FieldDefinitionNode $fieldNode, array &$existingArgs, DocumentNode $document): void
    {
        foreach ($fieldNode->directives as $directiveNode) {
            $directive = $this->instantiateDirective($directiveNode->name->value, $fieldNode, $directiveNode);

            if ($directive instanceof FieldResolverInterface) {
                $directive->setModel($this->getNamedType($fieldNode->type));
                $intent->setResolver($directive);

                if (method_exists($directive, 'modifyFieldType')) {
                    $directive->modifyFieldType($fieldNode, $document);
                }
            }

            if ($directive instanceof ArgumentBuilderDirectiveInterface) {
                if ($directive instanceof FieldArgumentDirectiveInterface) {
                    foreach ($directive->argumentNodes() as $argNode) {
                        $intent->addArgumentDirective($argNode->name->value, $directive);
                        $this->injectDirectiveArguments($fieldNode, $directive, $directiveNode, $existingArgs);
                    }
                } else {
                    $intent->addArgumentDirective($directiveNode->name->value, $directive);
                }
            }
        }
    }

    private function getObjectTypeDefinition(string $typeName): ?ObjectTypeDefinitionNode
    {
        foreach ($this->inputTypes as $name => $def) {
            if ($name === $typeName) {
                return $def;
            }
        }
        return null;
    }

    private function instantiateDirective(string $name, object $definitionNode, object $directiveNode): AbstractDirective
    {
        $directive = clone $this->directiveLocator->get($this->directiveRegistry->get($name));
        $directive->directiveNode = $directiveNode;
        $directive->definitionNode = $definitionNode;
        return $directive;
    }

    private function injectDirectiveArguments(FieldDefinitionNode $fieldNode, FieldArgumentDirectiveInterface $directive, object $directiveNode, array &$existingArgs): void
    {
        foreach ($directive->argumentNodes() as $argNode) {
            $name = $argNode->name->value;
            if (isset($existingArgs[$name])) {
                throw new \RuntimeException(sprintf(
                    'Argument "%s" on field "%s" conflicts with system argument added by @%s',
                    $name, $fieldNode->name->value, $directiveNode->name->value
                ));
            }

            $fieldNode->arguments[] = $argNode;
            $existingArgs[$name] = true;
        }
    }
}
