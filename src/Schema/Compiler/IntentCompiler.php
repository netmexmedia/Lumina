<?php

namespace Netmex\Lumina\Schema\Compiler;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use Netmex\Lumina\Contracts\ArgumentBuilderDirectiveInterface;
use Netmex\Lumina\Contracts\FieldResolverInterface;
use Netmex\Lumina\Directives\AbstractDirective;
use Netmex\Lumina\Directives\Registry\DirectiveRegistry;
use Netmex\Lumina\Intent\Intent;
use Netmex\Lumina\Intent\IntentRegistry;
use Symfony\Component\DependencyInjection\ServiceLocator;

final readonly class IntentCompiler
{
    private DirectiveRegistry $directives;
    private IntentRegistry $intentRegistry;
    private ServiceLocator $serviceLocator;

    public function __construct(DirectiveRegistry $directives, IntentRegistry $intentRegistry, ServiceLocator $serviceLocator) {
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

            $this->compileType($definition);
        }

        return $this->intentRegistry;
    }

    private function compileType(TypeDefinitionNode $typeNode): void
    {
        $typeName = $typeNode->name->value;

        foreach ($typeNode->fields as $fieldNode) {
            $intent = $this->buildIntent($typeName, $fieldNode);
            $this->intentRegistry->add($intent);
        }
    }

    private function buildIntent(string $typeName, FieldDefinitionNode $fieldNode): Intent
    {
        $intent = new Intent($typeName, $fieldNode->name->value);

        $this->applyFieldDirectives($intent, $fieldNode);
        $this->applyArgumentDirectives($intent, $fieldNode);

        return $intent;
    }

    private function applyFieldDirectives(Intent $intent, FieldDefinitionNode $fieldNode): void
    {
        foreach ($fieldNode->directives as $directiveNode) {
            $directive = $this->instantiateDirective(
                $directiveNode->name->value,
                $fieldNode,
                $directiveNode
            );

            if ($directive instanceof FieldResolverInterface) {
                $intent->setResolver($directive);
            }
        }
    }

    private function applyArgumentDirectives(Intent $intent, FieldDefinitionNode $fieldNode): void
    {
        foreach ($fieldNode->arguments as $argNode) {
            foreach ($argNode->directives as $directiveNode) {
                $directive = $this->instantiateDirective(
                    $directiveNode->name->value,
                    $argNode,
                    $directiveNode
                );

                if ($directive instanceof ArgumentBuilderDirectiveInterface) {
                    $intent->addArgumentDirective($argNode->name->value, $directive);
                }
            }
        }
    }

    private function instantiateDirective(string $name, object $definitionNode, object $directiveNode): AbstractDirective {
        $directive = clone $this->serviceLocator->get(
            $this->directives->get($name)
        );

        $directive->directiveNode = $directiveNode;
        $directive->definitionNode = $definitionNode;

        return $directive;
    }
}
