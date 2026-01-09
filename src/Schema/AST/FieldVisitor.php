<?php

namespace Netmex\Lumina\Schema\AST;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\FieldDefinitionNode;
use Netmex\Lumina\Directives\Registry\DirectiveRegistry;
use Netmex\Lumina\Intent\Intent;
use Symfony\Component\DependencyInjection\ServiceLocator;

final  class FieldVisitor extends ASTDirectiveVisitorBase
{
    public function __construct(
        private InputArgumentVisitor $inputVisitor
    ) {}

    public function visitField(
        Intent $intent,
        FieldDefinitionNode $fieldNode,
        array $inputTypes,
        DocumentNode $document
    ): void {
        $existingArgs = [];
        foreach ($fieldNode->arguments as $argNode) {
            $existingArgs[$argNode->name->value] = true;
        }

        $this->applyFieldDirectives(
            $intent,
            $fieldNode,
            $existingArgs,
            $document
        );

        // Input traversal
        foreach ($fieldNode->arguments as $argNode) {
            $this->inputVisitor->visitInputArgument(
                $intent,
                $argNode,
                $inputTypes
            );
        }

        // Return type traversal
        $returnType = $this->getNamedType($fieldNode->type);
        $this->inputVisitor->traverseReturnTypeFields(
            $intent,
            $returnType
        );
    }

    protected function getDirectiveLocator(): ServiceLocator
    {
        return $this->inputVisitor->getDirectiveLocator();
    }
    protected function getDirectiveRegistry(): DirectiveRegistry
    {
        return $this->inputVisitor->getDirectiveRegistry();
    }
}
