<?php

namespace Netmex\Lumina\Schema\AST;

use GraphQL\Error\SyntaxError;
use GraphQL\Language\AST\DirectiveDefinitionNode;
use GraphQL\Language\AST\EnumTypeDefinitionNode;
use GraphQL\Language\AST\EnumTypeExtensionNode;
use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputObjectTypeExtensionNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeExtensionNode;
use GraphQL\Language\AST\NodeList;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\AST\ObjectTypeExtensionNode;
use GraphQL\Language\AST\ScalarTypeDefinitionNode;
use GraphQL\Language\AST\ScalarTypeExtensionNode;
use GraphQL\Language\AST\SchemaExtensionNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\AST\TypeExtensionNode;
use GraphQL\Language\AST\UnionTypeDefinitionNode;
use GraphQL\Language\AST\UnionTypeExtensionNode;
use GraphQL\Language\Parser;

class ASTDocument
{
    public const TYPES = 'types';
    public const DIRECTIVES = 'directives';
    public const CLASS_NAME_TO_OBJECT_TYPE_NAME = 'classNameToObjectTypeName';
    public const SCHEMA_EXTENSIONS = 'schemaExtensions';
    public const HASH = 'hash';

    public array|NodeList $types = [];
    public array $typeExtensions = [];
    public array|NodeList $directives = [];
    public array $classNameToObjectTypeNames = [];
    public array $schemaExtensions = [];
    public string $hash;

    public static function fromSource(string $schema): self
    {
        try {
            $documentNode = Parser::parse($schema, ['noLocation' => true]);
        } catch (SyntaxError $e) {

        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }

        $instance = new static();
        $instance->hash = hash('sha256', $schema);

        foreach ($documentNode->definitions as $definition) {
            if ($definition instanceof TypeDefinitionNode) {
                $name = $definition->name->value;

                $instance->types[$name] = $definition;

                if ($definition instanceof ObjectTypeExtensionNode) {
//                    $modelName = ModelDirective::modelClass($definition);
//                    if ($modelName === null) {
//                        continue;
//                    }
//
//                    // Should be actual entity name space
//                    $namespacesToTry = (array) config('lighthouse.namespaces.models');
//                    $modelClass = Utils::namespaceClassName(
//                        $modelName,
//                        $namespacesToTry,
//                        static fn (string $classCandidate): bool => is_subclass_of($classCandidate, Entity::class),
//                    );
//
//                    if ($modelClass === null) {
//                        $consideredNamespaces = implode(', ', $namespacesToTry);
//                        throw new \RuntimeException("Failed to find a model class {$modelName} in namespaces [{$consideredNamespaces}] referenced in @model on type {$name}.");
//                    }
//
//                    $instance->classNameToObjectTypeNames[$modelClass][] = $name;
                }
            } elseif ($definition instanceof TypeExtensionNode) {
                $instance->typeExtensions[$definition->getName()->value][] = $definition;
            } elseif ($definition instanceof DirectiveDefinitionNode) {
                $instance->directives[$definition->name->value] = $definition;
            } elseif ($definition instanceof SchemaExtensionNode) {
                $instance->schemaExtensions[] = $definition;
            } else {
                throw new \RuntimeException('Unknown definition node type: ' . get_class($definition));
            }
        }

        return $instance;
    }
}