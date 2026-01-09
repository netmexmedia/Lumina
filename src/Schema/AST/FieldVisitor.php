<?php

namespace Netmex\Lumina\Schema\AST;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\FieldDefinitionNode;
use Netmex\Lumina\Directives\Registry\DirectiveRegistry;
use Netmex\Lumina\Intent\Intent;
use Netmex\Lumina\Schema\ASTDirectiveVisitorBase;
use Symfony\Component\DependencyInjection\ServiceLocator;

final  class FieldVisitor extends ASTDirectiveVisitorBase
{
    public function __construct(
        private readonly ArgumentDirectiveVisitor $argumentVisitor
    ) {}

    public function visitField(
        Intent $intent,
        FieldDefinitionNode $fieldNode,
        array $inputTypes,
        DocumentNode $document
    ): void {
        $existingArgs = $this->collectExistingArgs($fieldNode);

        $this->applyDirectives($intent, $fieldNode, $existingArgs, $document);

        $this->visitFieldArguments($intent, $fieldNode, $inputTypes);

        $this->visitReturnTypeFields($intent, $fieldNode);
    }

    private function collectExistingArgs(FieldDefinitionNode $fieldNode): array
    {
        $existingArgs = [];
        foreach ($fieldNode->arguments as $argNode) {
            $existingArgs[$argNode->name->value] = true;
        }
        return $existingArgs;
    }

    private function applyDirectives(
        Intent $intent,
        FieldDefinitionNode $fieldNode,
        array &$existingArgs,
        DocumentNode $document
    ): void {
        $this->applyFieldDirectives($intent, $fieldNode, $existingArgs, $document);
    }

    private function visitFieldArguments(
        Intent $intent,
        FieldDefinitionNode $fieldNode,
        array $inputTypes
    ): void {
        foreach ($fieldNode->arguments as $argNode) {
            $this->argumentVisitor->visitArgument($intent, $argNode, $inputTypes);
        }
    }

    private function visitReturnTypeFields(Intent $intent, FieldDefinitionNode $fieldNode): void
    {
        $returnType = $this->getNamedType($fieldNode->type);
        $this->argumentVisitor->traverseReturnTypeFields($intent, $returnType);
    }


    protected function getDirectiveLocator(): ServiceLocator
    {
        return $this->argumentVisitor->getDirectiveLocator();
    }
    protected function getDirectiveRegistry(): DirectiveRegistry
    {
        return $this->argumentVisitor->getDirectiveRegistry();
    }
}
