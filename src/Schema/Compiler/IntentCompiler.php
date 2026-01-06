<?php

namespace Netmex\Lumina\Schema\Compiler;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use Netmex\Lumina\Directives\AbstractDirective;
use Netmex\Lumina\Directives\Registry\DirectiveRegistry;
use Netmex\Lumina\Intent\Builder\IntentBuilder;
use Netmex\Lumina\Intent\Builder\IntentBuilderInterface;
use Netmex\Lumina\Intent\Intent;
use Netmex\Lumina\Intent\IntentRegistry;
use Symfony\Component\DependencyInjection\ServiceLocator;

final readonly class IntentCompiler
{
    private DirectiveRegistry $directives;
    private IntentRegistry $intentRegistry;
    private ServiceLocator $serviceLocator;

    public function __construct(
        DirectiveRegistry $directives,
        IntentRegistry $intentRegistry,
        ServiceLocator $serviceLocator
    ) {
        $this->directives = $directives;
        $this->intentRegistry = $intentRegistry;
        $this->serviceLocator = $serviceLocator;
    }

    public function compile(DocumentNode $document): IntentRegistry
    {
        foreach ($document->definitions as $definition) {
            if (!$definition instanceof TypeDefinitionNode) {
                continue;
            }

            foreach ($definition->fields as $fieldDefinitionNode) {
                $this->compileField($definition, $fieldDefinitionNode);
            }
        }

        return $this->intentRegistry;
    }

    private function compileField(TypeDefinitionNode $typeDefinitionNode, FieldDefinitionNode $fieldDefinitionNode): void
    {
        $this->applyDirectives($fieldDefinitionNode->directives, $fieldDefinitionNode);
        foreach ($fieldDefinitionNode->arguments as $argNode) {
            $this->applyDirectives($argNode->directives, $argNode);
        }
    }

    private function instantiateDirective(string $name, object $definitionNode, object $directiveNode): AbstractDirective
    {
        $directive = clone $this->serviceLocator->get($this->directives->get($name));
        $directive->directiveNode = $directiveNode;
        $directive->definitionNode = $definitionNode;

        return $directive;
    }

    private function applyDirectives(iterable $directiveNodes, object $definitionNode): void
    {
        foreach ($directiveNodes as $directiveNode) {
            $directiveName = $directiveNode->name->value;
            $directive = $this->instantiateDirective($directiveName, $definitionNode, $directiveNode);
            $this->intentRegistry->add($directiveName, $directive);
        }
    }
}
