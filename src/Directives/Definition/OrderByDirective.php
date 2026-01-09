<?php

namespace Netmex\Lumina\Directives\Definition;

use Doctrine\ORM\QueryBuilder;
use GraphQL\Language\AST\EnumTypeDefinitionNode;
use GraphQL\Language\AST\EnumValueDefinitionNode;
use GraphQL\Language\AST\EnumValueNode;
use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\ListTypeNode;
use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Language\AST\NameNode;
use GraphQL\Language\AST\NodeList;
use GraphQL\Language\AST\NonNullTypeNode;
use Netmex\Lumina\Contracts\ArgumentBuilderDirectiveInterface;
use Netmex\Lumina\Contracts\DirectiveEnumTypesInterface;
use Netmex\Lumina\Contracts\DirectiveInputObjectTypesInterface;
use Netmex\Lumina\Contracts\FieldArgumentDirectiveInterface;
use Netmex\Lumina\Directives\AbstractDirective;

class OrderByDirective extends AbstractDirective implements ArgumentBuilderDirectiveInterface, FieldArgumentDirectiveInterface, DirectiveEnumTypesInterface, DirectiveInputObjectTypesInterface
{
    public static function name(): string
    {
        return 'orderBy';
    }

    public static function definition(): string
    {
        // TODO: Quick fix to avoid re-declaring OrderByDirection enum multiple times
        // This is not ideal and should be refactored in the future and use EnumTypes as well inputObjectTypes
        return <<<'GRAPHQL'
            enum OrderByDirection {
                ASC
                DESC
            }

            directive @orderBy(
                columns: [String!],
                direction: OrderByDirection = ASC,
            ) repeatable on ARGUMENT_DEFINITION | INPUT_FIELD_DEFINITION | FIELD_DEFINITION
        GRAPHQL;
    }

    public function enumTypes(): array
    {
        $columns = $this->getArgument('columns');
        $enumName = ucfirst($this->getColumn()) . 'OrderByColumn';

        return [
            new EnumTypeDefinitionNode([
                'name' => new NameNode(['value' => 'OrderByDirection']),
                'values' => new NodeList([
                    new EnumValueDefinitionNode(['name' => new NameNode(['value' => 'ASC']), 'directives' => new NodeList([])]),
                    new EnumValueDefinitionNode(['name' => new NameNode(['value' => 'DESC']), 'directives' => new NodeList([])]),
                ]),
                'directives' => new NodeList([]),
            ]),
            new EnumTypeDefinitionNode([
                'name' => new NameNode(['value' => $enumName]),
                'values' => new NodeList(array_map(fn($col) => new EnumValueDefinitionNode([
                    'name' => new NameNode(['value' => $col]),
                    'directives' => new NodeList([]),
                ]), $columns)),
                'directives' => new NodeList([]),
            ])
        ];
    }

    public function inputObjectTypes(): array
    {
        $inputName = ucfirst($this->getColumn()) . 'OrderByInput';
        $enumName = ucfirst($this->getColumn()) . 'OrderByColumn';

        return [
            new InputObjectTypeDefinitionNode([
                'name' => new NameNode(['value' => $inputName]),
                'fields' => new NodeList([
                    new InputValueDefinitionNode([
                        'name' => new NameNode(['value' => 'columns']),
                        'type' => new ListTypeNode([
                            'type' => new NonNullTypeNode([
                                'type' => new NamedTypeNode([
                                    'name' => new NameNode(['value' => $enumName])
                                ])
                            ])
                        ]),
                        'directives' => new NodeList([]),

                    ]),
                    new InputValueDefinitionNode([
                        'name' => new NameNode(['value' => 'direction']),
                        'type' => new NamedTypeNode([
                            'name' => new NameNode(['value' => 'OrderByDirection'])
                        ]),
                        'directives' => new NodeList([]),
                        'defaultValue' => new EnumValueNode(['value' => 'ASC']),
                    ]),
                ]),
            ])
        ];
    }

    public function argumentNodes(): array
    {
        $orderByInputName = ucfirst($this->getColumn()) . 'OrderByInput';

        return [
            new InputValueDefinitionNode([
                'name' => new NameNode(['value' => 'orderBy']),
                'type' => new NamedTypeNode([
                    'name' => new NameNode(['value' => $orderByInputName])
                ]),
                'directives' => new NodeList([]),
            ]),
        ];
    }

    public function handleArgumentBuilder(QueryBuilder $queryBuilder, $value): QueryBuilder
    {
        if ($value === null) {
            return $queryBuilder;
        }

        $alias = current($queryBuilder->getRootAliases());
        $columns = $value['columns'] ?? [];
        $direction = strtoupper($value['direction'] ?? 'ASC');

        if (!in_array($direction, ['ASC', 'DESC'], true)) {
            $direction = 'ASC';
        }

        foreach ($columns as $column) {
            $queryBuilder->addOrderBy("$alias.$column", $direction);
        }

        return $queryBuilder;
    }
}