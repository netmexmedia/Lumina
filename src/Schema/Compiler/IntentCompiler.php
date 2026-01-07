<?php

declare(strict_types=1);

namespace Netmex\Lumina\Schema\Compiler;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use Netmex\Lumina\Contracts\ArgumentBuilderDirectiveInterface;
use Netmex\Lumina\Contracts\FieldResolverInterface;
use Netmex\Lumina\Contracts\SchemaSourceInterface;
use Netmex\Lumina\Directives\AbstractDirective;
use Netmex\Lumina\Directives\Registry\DirectiveRegistry;
use Netmex\Lumina\Intent\Intent;
use Netmex\Lumina\Intent\IntentRegistry;
use Symfony\Component\DependencyInjection\ServiceLocator;

final readonly class IntentCompiler
{
    private DirectiveRegistry $directives;
    private IntentRegistry $intentRegistry;
    private SchemaSourceInterface $schemaSource;
    private ServiceLocator $serviceLocator;

    public function __construct(DirectiveRegistry $directives, IntentRegistry $intentRegistry, SchemaSourceInterface $schemaSource, ServiceLocator $serviceLocator) {
        $this->directives = $directives;
        $this->intentRegistry = $intentRegistry;
        $this->schemaSource = $schemaSource;
        $this->serviceLocator = $serviceLocator;
    }

    public function compile(): IntentRegistry
    {
        $document = $this->schemaSource->document();

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

        // Step 1: collect type-level directives
        $typeDirectives = $this->collectTypeDirectives($typeNode);

        // Step 2: build and register field intents
        foreach ($typeNode->fields as $fieldNode) {
            $intent = $this->buildIntent($typeName, $fieldNode);

            // Step 3: attach type directives to each field intent
            $this->applyTypeDirectivesToIntent($intent, $typeDirectives);

            $this->intentRegistry->add($intent);
        }
    }

    /**
     * Collect all directives applied to a type
     */
    private function collectTypeDirectives(TypeDefinitionNode $typeNode): array
    {
        $directives = [];
        foreach ($typeNode->directives as $directiveNode) {
            $directives[] = $this->instantiateDirective(
                $directiveNode->name->value,
                $typeNode,
                $directiveNode
            );
        }
        return $directives;
    }

    /**
     * Apply collected type-level directives to a field intent
     */
    private function applyTypeDirectivesToIntent(Intent $intent, array $typeDirectives): void
    {
        foreach ($typeDirectives as $directive) {
            $intent->applyTypeDirective($directive->name(), $directive);
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
