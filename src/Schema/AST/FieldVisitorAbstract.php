<?php

declare(strict_types=1);

namespace Netmex\Lumina\Schema\AST;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\FieldDefinitionNode;
use Netmex\Lumina\Contracts\DirectiveFactoryInterface;
use Netmex\Lumina\Directives\Registry\DirectiveRegistry;
use Netmex\Lumina\Intent\Intent;
use Symfony\Component\DependencyInjection\ServiceLocator;

final class FieldVisitorAbstract extends AbstractASTDirectiveVisitor
{
    private ArgumentDirectiveVisitorAbstract $argumentVisitor;

    public function __construct(DirectiveFactoryInterface $directiveFactory, ArgumentDirectiveVisitorAbstract $argumentVisitor)
    {
        parent::__construct($directiveFactory);
        $this->argumentVisitor = $argumentVisitor;
    }

    public function visitField(Intent $intent, FieldDefinitionNode $fieldNode, array $inputTypes, DocumentNode $document): void
    {
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

    private function applyDirectives(Intent $intent, FieldDefinitionNode $fieldNode, array &$existingArgs, DocumentNode $document): void
    {
        $this->applyFieldDirectives($intent, $fieldNode, $existingArgs, $document);
    }

    private function visitFieldArguments(Intent $intent, FieldDefinitionNode $fieldNode, array $inputTypes): void
    {
        foreach ($fieldNode->arguments as $argNode) {
            $this->argumentVisitor->visitArgument($intent, $argNode, $fieldNode, $inputTypes);
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
