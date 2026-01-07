<?php

declare(strict_types=1);

namespace Netmex\Lumina\Schema\Compiler;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use Netmex\Lumina\Contracts\ArgumentBuilderDirectiveInterface;
use Netmex\Lumina\Contracts\FieldResolverInterface;
use Netmex\Lumina\Contracts\SchemaSourceInterface;
use Netmex\Lumina\Directives\AbstractDirective;
use Netmex\Lumina\Directives\Registry\DirectiveRegistry;
use Netmex\Lumina\Intent\Intent;
use Netmex\Lumina\Intent\IntentRegistry;
use Symfony\Component\DependencyInjection\ServiceLocator;

final class IntentCompiler
{
    private DirectiveRegistry $directives;
    private IntentRegistry $intentRegistry;
    private SchemaSourceInterface $schemaSource;
    private ServiceLocator $serviceLocator;
    private array $inputTypes = [];

    public function __construct(
        DirectiveRegistry $directives,
        IntentRegistry $intentRegistry,
        SchemaSourceInterface $schemaSource,
        ServiceLocator $serviceLocator
    ) {
        $this->directives = $directives;
        $this->intentRegistry = $intentRegistry;
        $this->schemaSource = $schemaSource;
        $this->serviceLocator = $serviceLocator;
    }

    public function compile(): IntentRegistry
    {
        $document = $this->schemaSource->document();

        $this->registerInputTypes($document);
        $this->compileTypes($document);

        return $this->intentRegistry;
    }

    private function registerInputTypes(DocumentNode $document): void
    {
        foreach ($document->definitions as $definition) {
            if ($definition instanceof InputObjectTypeDefinitionNode) {
                $this->inputTypes[$definition->name->value] = $definition;
            }
        }
    }

    private function compileTypes(DocumentNode $document): void
    {
        foreach ($document->definitions as $definition) {
            if ($definition instanceof TypeDefinitionNode) {
                $this->compileType($definition);
            }
        }
    }

    private function compileType(TypeDefinitionNode $typeNode): void
    {
        $intentTypeDirectives = $this->collectTypeDirectives($typeNode);

        foreach ($typeNode->fields as $fieldNode) {
            if (!($fieldNode instanceof FieldDefinitionNode)) {
                continue;
            }

            $intent = new Intent($typeNode->name->value, $fieldNode->name->value);
            $this->applyTypeDirectivesToIntent($intent, $intentTypeDirectives);
            $this->applyFieldDirectives($intent, $fieldNode);

            foreach ($fieldNode->arguments as $argNode) {
                $this->applyArgumentDirectives($intent, $argNode);
            }

            $this->intentRegistry->add($intent);
        }
    }

    public function applyArgumentDirectives(Intent $intent, InputValueDefinitionNode $argNode, string $path = ''): void
    {
        $argPath = $path ? $path . '.' . $argNode->name->value : $argNode->name->value;

        foreach ($argNode->directives as $directiveNode) {
            $directive = $this->instantiateDirective($directiveNode->name->value, $argNode, $directiveNode);

            if ($directive instanceof ArgumentBuilderDirectiveInterface) {
                $intent->addArgumentDirective($argPath, $directive);
            }
        }

        $namedType = $this->getNamedType($argNode->type);
        if (!isset($this->inputTypes[$namedType])) {
            return;
        }

        foreach ($this->inputTypes[$namedType]->fields as $nestedArg) {
            $this->applyArgumentDirectives($intent, $nestedArg, $argPath);
        }
    }

    private function getNamedType(object $typeNode): string
    {
        if (property_exists($typeNode, 'name') && $typeNode->name !== null) {
            return $typeNode->name->value;
        }

        if (property_exists($typeNode, 'type') && $typeNode->type !== null) {
            return $this->getNamedType($typeNode->type);
        }

        throw new \RuntimeException('Cannot resolve named type from AST node');
    }

    private function collectTypeDirectives(TypeDefinitionNode $typeNode): array
    {
        $directives = [];
        foreach ($typeNode->directives as $directiveNode) {
            $directives[] = $this->instantiateDirective($directiveNode->name->value, $typeNode, $directiveNode);
        }
        return $directives;
    }

    private function applyTypeDirectivesToIntent(Intent $intent, array $directives): void
    {
        foreach ($directives as $directive) {
            $intent->applyTypeDirective($directive->name(), $directive);
        }
    }

    private function applyFieldDirectives(Intent $intent, FieldDefinitionNode $fieldNode): void
    {
        foreach ($fieldNode->directives as $directiveNode) {
            $directive = $this->instantiateDirective($directiveNode->name->value, $fieldNode, $directiveNode);

            if ($directive instanceof FieldResolverInterface) {
                $intent->setResolver($directive);
            }
        }
    }

    private function instantiateDirective(string $name, object $definitionNode, object $directiveNode): AbstractDirective
    {
        $directive = clone $this->serviceLocator->get($this->directives->get($name));
        $directive->directiveNode = $directiveNode;
        $directive->definitionNode = $definitionNode;
        return $directive;
    }
}
