<?php

namespace Netmex\Lumina\Schema\AST;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\FieldDefinitionNode;
use Netmex\Lumina\Intent\Intent;
use Netmex\Lumina\Directives\Registry\DirectiveRegistry;
use Symfony\Component\DependencyInjection\ServiceLocator;

final readonly class FieldVisitor
{
    public function __construct(
        private InputArgumentVisitor $inputArgumentVisitor,
    ) {}

    public function visitField(Intent $intent, FieldDefinitionNode $fieldNode, array $inputTypes, DocumentNode $document): void
    {
        $existingArgs = [];
        $this->applyFieldDirectives($intent, $fieldNode, $existingArgs, $document);

        foreach ($fieldNode->arguments as $argNode) {
            $this->inputArgumentVisitor->visitInputArgument($intent, $argNode, $inputTypes, $document);
        }

        $returnType = $this->inputArgumentVisitor->getNamedType($fieldNode->type);
        $this->inputArgumentVisitor->traverseReturnTypeFields($intent, $returnType);
    }

    public function collectTypeDirectives($typeNode): array
    {
        return $this->inputArgumentVisitor->collectTypeDirectives($typeNode);
    }

    public function applyTypeDirectivesToIntent($intent, array $typeDirectives): void
    {
        $this->inputArgumentVisitor->applyTypeDirectivesToIntent($intent, $typeDirectives);
    }

    private function applyFieldDirectives(Intent $intent, FieldDefinitionNode $fieldNode, array &$existingArgs, DocumentNode $document): void
    {
        $this->inputArgumentVisitor->applyFieldDirectives($intent, $fieldNode, $existingArgs, $document);
    }
}
