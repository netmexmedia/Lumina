<?php

declare(strict_types=1);

namespace Netmex\Lumina\Schema\Compiler;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use Netmex\Lumina\Contracts\ArgumentBuilderDirectiveInterface;
use Netmex\Lumina\Contracts\FieldArgumentDirectiveInterface;
use Netmex\Lumina\Contracts\FieldResolverInterface;
use Netmex\Lumina\Directives\AbstractDirective;
use Netmex\Lumina\Directives\Registry\DirectiveRegistry;
use Netmex\Lumina\Intent\Intent;
use Netmex\Lumina\Intent\IntentRegistry;
use Netmex\Lumina\Schema\Source\SchemaSourceRegistry;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * @Deprecated Use AST Compiler instead with appropriate passes instead.
 * Compiles GraphQL schema AST into an IntentRegistry by processing types, fields,
 * and directives to build a structured representation of intents.
 */
final class IntentCompiler
{
    private DirectiveRegistry $directives;
    private IntentRegistry $intentRegistry;
    private SchemaSourceRegistry $schemaSource;
    private ServiceLocator $directiveLocator;
    private array $inputTypes = [];

    public function __construct(
        DirectiveRegistry $directives,
        IntentRegistry $intentRegistry,
        SchemaSourceRegistry $schemaSource,
        ServiceLocator $directiveLocator
    ) {
        $this->directives = $directives;
        $this->intentRegistry = $intentRegistry;
        $this->schemaSource = $schemaSource;
        $this->directiveLocator = $directiveLocator;
    }

    public function compile(): IntentRegistry
    {
        $document = $this->schemaSource->getDocument();

        if (!$document) {
            throw new \RuntimeException('Schema document is missing from schema source.');
        }

        $this->indexInputTypes($document);
        $this->compileTypes($document);
        $this->finalizeDocument($document);

        return $this->intentRegistry;
    }

    // ---------------------------------------------
    // Document & Type Level
    // ---------------------------------------------

    private function indexInputTypes(DocumentNode $document): void
    {
        foreach ($document->definitions as $def) {
            if ($def instanceof InputObjectTypeDefinitionNode) {
                $this->inputTypes[$def->name->value] = $def;
            }
        }
    }

    private function compileTypes(DocumentNode $document): void
    {
        foreach ($document->definitions as $def) {
            if ($def instanceof TypeDefinitionNode) {
                $this->compileType($def);
            }
        }
    }

    private function finalizeDocument(DocumentNode $document): void
    {
        if (method_exists($this->schemaSource, 'setDocument')) {
            $this->schemaSource->setDocument($document);
        }
    }

    private function compileType(TypeDefinitionNode $typeNode): void
    {
        $typeName = $typeNode->name->value;
        $typeDirectives = $this->collectTypeDirectives($typeNode);

        foreach ($typeNode->fields as $fieldNode) {
            if (!$fieldNode instanceof FieldDefinitionNode) {
                continue;
            }

            $intent = new Intent($typeName, $fieldNode->name->value);

            $this->applyTypeDirectivesToIntent($intent, $typeDirectives);
            $this->compileField($intent, $fieldNode);

            $this->intentRegistry->add($intent);
        }
    }

    private function applyTypeDirectivesToIntent(Intent $intent, array $typeDirectives): void
    {
        foreach ($typeDirectives as $directive) {
            $intent->applyTypeDirective($directive->name(), $directive);
        }
    }

    // ---------------------------------------------
    // Field Level
    // ---------------------------------------------

    private function compileField(Intent $intent, FieldDefinitionNode $fieldNode): void
    {
        $existingArgs = [];
        foreach ($fieldNode->arguments as $argNode) {
            $existingArgs[$argNode->name->value] = true;
        }

        $this->applyFieldDirectives($intent, $fieldNode, $existingArgs);

        foreach ($fieldNode->arguments as $argNode) {
            $this->compileInputArgument($intent, $argNode);
        }

        $returnTypeName = $this->getNamedType($fieldNode->type);
        $this->traverseReturnTypeFields($intent, $returnTypeName);
    }

    private function applyFieldDirectives(Intent $intent, FieldDefinitionNode $fieldNode, array &$existingArgs): void
    {
        foreach ($fieldNode->directives as $directiveNode) {
            $directive = $this->instantiateDirective(
                $directiveNode->name->value,
                $fieldNode,
                $directiveNode
            );

            if ($directive instanceof FieldResolverInterface) {
                $directive->setModel($this->getNamedType($fieldNode->type));
                $intent->setResolver($directive);

                if (method_exists($directive, 'modifyFieldType')) {
                    $directive->modifyFieldType($fieldNode, $this->schemaSource->getDocument());
                }
            }

            if ($directive instanceof ArgumentBuilderDirectiveInterface) {
                if ($directive instanceof FieldArgumentDirectiveInterface) {
                    foreach ($directive->argumentNodes() as $argNode) {
                        $intent->addArgumentDirective($argNode->name->value, $directive);
                    }
                } else {
                    $intent->addArgumentDirective($directiveNode->name->value, $directive);
                }
            }

            if ($directive instanceof FieldArgumentDirectiveInterface) {
                $this->injectDirectiveArguments($fieldNode, $directive, $directiveNode, $existingArgs);
            }
        }
    }

    // ---------------------------------------------
    // Input Argument Level (recursive)
    // ---------------------------------------------

    private function compileInputArgument(Intent $intent, InputValueDefinitionNode $argNode, string $parentPath = ''): void
    {
        $argPath = $this->buildArgumentPath($argNode, $parentPath);

        $this->applyArgumentNodeDirectives($intent, $argNode, $argPath);

        $namedType = $this->getNamedType($argNode->type);
        if (isset($this->inputTypes[$namedType])) {
            foreach ($this->inputTypes[$namedType]->fields as $nestedArg) {
                $this->compileInputArgument($intent, $nestedArg, $argPath);
            }
        }
    }

    private function applyArgumentNodeDirectives(Intent $intent, InputValueDefinitionNode $argNode, string $argPath): void
    {
        foreach ($argNode->directives as $directiveNode) {
            $directive = $this->instantiateDirective(
                $directiveNode->name->value,
                $argNode,
                $directiveNode
            );

            if ($directive instanceof ArgumentBuilderDirectiveInterface) {
                $intent->addArgumentDirective($argPath, $directive);
            }
        }
    }

    // ---------------------------------------------
    // Return Type Traversal
    // ---------------------------------------------

    private function traverseReturnTypeFields(Intent $intent, string $typeName, string $prefix = ''): void
    {
        $typeDef = $this->getObjectTypeDefinition($typeName);
        if (!$typeDef) {
            return;
        }

        foreach ($typeDef->fields as $fieldNode) {
            $fieldPath = $prefix === '' ? $fieldNode->name->value : $prefix . '.' . $fieldNode->name->value;

            foreach ($fieldNode->directives as $directiveNode) {
                $directive = $this->instantiateDirective(
                    $directiveNode->name->value,
                    $fieldNode,
                    $directiveNode
                );

                if ($directive instanceof ArgumentBuilderDirectiveInterface) {
                    $intent->addArgumentDirective($fieldPath, $directive);
                }
            }

            $nestedType = $this->getNamedType($fieldNode->type);
            $this->traverseReturnTypeFields($intent, $nestedType, $fieldPath);
        }
    }

    // ---------------------------------------------
    // Utilities
    // ---------------------------------------------

    private function getObjectTypeDefinition(string $typeName): ?ObjectTypeDefinitionNode
    {
        $document = $this->schemaSource->getDocument();
        foreach ($document->definitions as $def) {
            if ($def instanceof ObjectTypeDefinitionNode && $def->name->value === $typeName) {
                return $def;
            }
        }
        return null;
    }

    private function buildArgumentPath(InputValueDefinitionNode $argNode, string $parentPath): string
    {
        return $parentPath === '' ? $argNode->name->value : $parentPath . '.' . $argNode->name->value;
    }

    private function injectDirectiveArguments(FieldDefinitionNode $fieldNode, FieldArgumentDirectiveInterface $directive, object $directiveNode, array &$existingArgs): void
    {
        foreach ($directive->argumentNodes() as $argNode) {
            $name = $argNode->name->value;

            if (isset($existingArgs[$name])) {
                throw new \RuntimeException(sprintf(
                    'Argument "%s" on field "%s" conflicts with a system argument added by @%s. ' .
                    'Please rename your argument to avoid reserved system names.',
                    $name,
                    $fieldNode->name->value,
                    $directiveNode->name->value
                ));
            }

            $fieldNode->arguments[] = $argNode;
            $existingArgs[$name] = true;
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
            $directives[] = $this->instantiateDirective(
                $directiveNode->name->value,
                $typeNode,
                $directiveNode
            );
        }
        return $directives;
    }

    private function instantiateDirective(string $name, object $definitionNode, object $directiveNode): AbstractDirective
    {
        $directive = clone $this->directiveLocator->get($this->directives->get($name));
        $directive->directiveNode = $directiveNode;
        $directive->definitionNode = $definitionNode;
        return $directive;
    }
}
