<?php

namespace Netmex\Lumina\Schema\AST;

use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use Netmex\Lumina\Contracts\ArgumentBuilderDirectiveInterface;
use Netmex\Lumina\Intent\Intent;
use Netmex\Lumina\Directives\Registry\DirectiveRegistry;
use Symfony\Component\DependencyInjection\ServiceLocator;

final class InputArgumentVisitor extends ASTDirectiveVisitorBase
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

    public function traverseReturnTypeFields(Intent $intent, string $typeName, string $prefix = ''): void
    {
        $typeDef = $this->getObjectTypeDefinition($typeName);
        if (!$typeDef) return;

        foreach ($typeDef->fields as $fieldNode) {
            $fieldPath = $prefix === '' ? $fieldNode->name->value : $prefix . '.' . $fieldNode->name->value;
            foreach ($fieldNode->directives as $directiveNode) {
                $directive = $this->instantiateDirective(
                    $directiveNode->name->value, $fieldNode, $directiveNode,
                    $this->directiveLocator, $this->directiveRegistry
                );
                if ($directive instanceof ArgumentBuilderDirectiveInterface) {
                    $intent->addArgumentDirective($fieldPath, $directive);
                }
            }

            $nestedType = $this->getNamedType($fieldNode->type);
            $this->traverseReturnTypeFields($intent, $nestedType, $fieldPath);
        }
    }

    public function getDirectiveLocator(): ServiceLocator
    {
        return $this->directiveLocator;
    }

    public function getDirectiveRegistry(): DirectiveRegistry
    {
        return $this->directiveRegistry;
    }

    private function getObjectTypeDefinition(string $typeName): ?ObjectTypeDefinitionNode
    {
        return $this->inputTypes[$typeName] ?? null;
    }

    private function buildArgumentPath(InputValueDefinitionNode $argNode, string $parentPath): string
    {
        return $parentPath === '' ? $argNode->name->value : $parentPath . '.' . $argNode->name->value;
    }

    private function applyArgumentNodeDirectives(Intent $intent, InputValueDefinitionNode $argNode, string $argPath): void
    {
        foreach ($argNode->directives as $directiveNode) {
            $directive = $this->instantiateDirective(
                $directiveNode->name->value, $argNode, $directiveNode,
                $this->directiveLocator, $this->directiveRegistry
            );

            if ($directive instanceof ArgumentBuilderDirectiveInterface) {
                $intent->addArgumentDirective($argPath, $directive);
            }
        }
    }
}
