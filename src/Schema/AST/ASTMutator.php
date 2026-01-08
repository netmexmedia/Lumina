<?php

namespace Netmex\Lumina\Schema\AST;

use GraphQL\Language\AST\TypeDefinitionNode;
use Netmex\Lumina\Contracts\SchemaSourceInterface;
use Netmex\Lumina\Directives\Registry\DirectiveRegistry;
use Symfony\Component\DependencyInjection\Argument\ServiceLocator;

final class ASTMutator
{
    private ServiceLocator $serviceLocator;
    private DirectiveRegistry $directives;
    private SchemaSourceInterface $schemaSource;

    public function __construct(DirectiveRegistry $directives, ServiceLocator $serviceLocator, SchemaSourceInterface $schemaSource)
    {
        $this->directives = $directives;
        $this->serviceLocator = $serviceLocator;
        $this->schemaSource = $schemaSource;
    }

    public function mutate(): void
    {
        $document = $this->schemaSource->getDocument();

        foreach ($document->definitions as $def) {
            if (!$def instanceof TypeDefinitionNode) continue;

            foreach ($def->fields as $fieldNode) {
                foreach ($fieldNode->directives as $directiveNode) {
                    $directive = clone $this->serviceLocator->get($this->directives->get($directiveNode->name->value));
                    $directive->directiveNode = $directiveNode;
                    $directive->definitionNode = $fieldNode;

                    if ($directive instanceof FieldArgumentDirectiveInterface) {
                        foreach ($directive->argumentNodes() as $argNode) {
                            $fieldNode->arguments[] = $argNode; // mutate AST
                        }
                    }
                }
            }
        }
    }
}