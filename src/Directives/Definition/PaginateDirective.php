<?php

declare(strict_types=1);

namespace Netmex\Lumina\Directives\Definition;

use Doctrine\ORM\QueryBuilder;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Language\AST\NameNode;
use GraphQL\Language\AST\NodeList;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Type\Definition\ResolveInfo;
use Netmex\Lumina\Context\Context;
use Netmex\Lumina\Contracts\FieldArgumentDirectiveInterface;
use Netmex\Lumina\Contracts\FieldInputDirectiveInterface;
use Netmex\Lumina\Contracts\FieldResolverInterface;
use Netmex\Lumina\Contracts\FieldTypeModifierInterface;
use Netmex\Lumina\Contracts\FieldValueInterface;
use Netmex\Lumina\Directives\AbstractDirective;
use Netmex\Lumina\Paginator\Paginator;

final class PaginateDirective extends AbstractDirective implements FieldResolverInterface, FieldArgumentDirectiveInterface, FieldInputDirectiveInterface, FieldTypeModifierInterface
{
    public static function name(): string
    {
        return 'paginate';
    }

    public static function definition(): string
    {
        return <<<'GRAPHQL'
            directive @paginate(
                model: String
                resolver: String
                limit: Int = 100
            ) on FIELD_DEFINITION
        GRAPHQL;
    }

    // Might do a AST mutator to add this input globally
    public static function inputsDefinition(): string
    {
        return <<<'GRAPHQL'
            input PaginateInput {
                page: Int
                limit: Int
            }
        GRAPHQL;
    }

    public function resolveField(FieldValueInterface $value, ?QueryBuilder $queryBuilder): callable
    {
        return static function ($root, array $args, Context $context, ResolveInfo $info) use ($queryBuilder)
        {
            $page = max(1, $args['page'] ?? 1);
            $limit = max(1, $args['first'] ?? 10);

            $pagination = Paginator::paginate($queryBuilder, $page, $limit);

            return [
                'data' => $pagination['items'],
                'paginatorInfo' => $pagination['paginatorInfo'],
            ];
        };
    }

    public function argumentNodes(): array
    {
        return [
            new InputValueDefinitionNode([
                'name' => new NameNode(['value' => 'page']),
                'type' => new NamedTypeNode([
                    'name' => new NameNode(['value' => 'Int'])
                ]),
                'directives' => new NodeList([]),
                'description' => null,
                'defaultValue' => null,
            ]),
            new InputValueDefinitionNode([
                'name' => new NameNode(['value' => 'first']),
                'type' => new NamedTypeNode([
                    'name' => new NameNode(['value' => 'Int'])
                ]),
                'directives' => new NodeList([]),
                'description' => null,
                'defaultValue' => null,
            ])
        ];
    }

    public function modifyFieldType(FieldDefinitionNode $fieldNode, DocumentNode $document): void
    {
        $this->ensurePaginatorInfo($document);
        $paginatedTypeName = $this->getOrCreatePaginatedType($fieldNode, $document);

        $fieldNode->type = new NamedTypeNode([
            'name' => new NameNode(['value' => $paginatedTypeName])
        ]);
    }

    private function ensurePaginatorInfo(DocumentNode $document): void
    {
        foreach ($document->definitions as $def) {
            if ($def instanceof ObjectTypeDefinitionNode && $def->name->value === 'PaginatorInfo') {
                return;
            }
        }

        $document->definitions[] = new ObjectTypeDefinitionNode([
            'name' => new NameNode(['value' => 'PaginatorInfo']),
            'fields' => new NodeList([
                new FieldDefinitionNode([
                    'name' => new NameNode(['value' => 'currentPage']),
                    'type' => new NamedTypeNode(['name' => new NameNode(['value' => 'Int'])]),
                    'directives' => new NodeList([]),
                    'arguments' => new NodeList([]),
                ]),
                new FieldDefinitionNode([
                    'name' => new NameNode(['value' => 'lastPage']),
                    'type' => new NamedTypeNode(['name' => new NameNode(['value' => 'Int'])]),
                    'directives' => new NodeList([]),
                    'arguments' => new NodeList([]),
                ]),
                new FieldDefinitionNode([
                    'name' => new NameNode(['value' => 'total']),
                    'type' => new NamedTypeNode(['name' => new NameNode(['value' => 'Int'])]),
                    'directives' => new NodeList([]),
                    'arguments' => new NodeList([]),
                ]),
                new FieldDefinitionNode([
                    'name' => new NameNode(['value' => 'perPage']),
                    'type' => new NamedTypeNode(['name' => new NameNode(['value' => 'Int'])]),
                    'directives' => new NodeList([]),
                    'arguments' => new NodeList([]),
                ]),
                new FieldDefinitionNode([
                    'name' => new NameNode(['value' => 'hasMorePages']),
                    'type' => new NamedTypeNode(['name' => new NameNode(['value' => 'Boolean'])]),
                    'directives' => new NodeList([]),
                    'arguments' => new NodeList([]),
                ]),
            ]),
            'directives' => new NodeList([]),
            'interfaces' => new NodeList([]),
        ]);
    }

    private function getOrCreatePaginatedType(FieldDefinitionNode $fieldNode, DocumentNode $document): string
    {
        $originalType = $fieldNode->type;
        $originalTypeName = $this->getNamedTypeName($originalType);
        $paginatedTypeName = 'Paginated' . $originalTypeName;

        foreach ($document->definitions as $def) {
            if ($def instanceof ObjectTypeDefinitionNode && $def->name->value === $paginatedTypeName) {
                return $paginatedTypeName;
            }
        }

        $paginatedTypeNode = new ObjectTypeDefinitionNode([
            'name' => new NameNode(['value' => $paginatedTypeName]),
            'fields' => new NodeList([
                new FieldDefinitionNode([
                    'name' => new NameNode(['value' => 'data']),
                    'type' => $originalType,
                    'directives' => new NodeList([]),
                    'arguments' => new NodeList([]),
                ]),
                new FieldDefinitionNode([
                    'name' => new NameNode(['value' => 'paginatorInfo']),
                    'type' => new NamedTypeNode(['name' => new NameNode(['value' => 'PaginatorInfo'])]),
                    'directives' => new NodeList([]),
                    'arguments' => new NodeList([]),
                ]),
            ]),
            'directives' => new NodeList([]),
            'interfaces' => new NodeList([]),
        ]);

        $document->definitions[] = $paginatedTypeNode;

        return $paginatedTypeName;
    }
}