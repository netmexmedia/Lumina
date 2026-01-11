<?php

namespace Netmex\Lumina\Directives\Definition;

use Doctrine\ORM\QueryBuilder;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Language\AST\NameNode;
use GraphQL\Language\AST\NodeList;
use GraphQL\Language\AST\NonNullTypeNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use Netmex\Lumina\Contracts\ArgumentBuilderDirectiveInterface;
use Netmex\Lumina\Contracts\FieldArgumentDirectiveInterface;
use Netmex\Lumina\Contracts\FieldInputDirectiveInterface;
use Netmex\Lumina\Directives\AbstractDirective;

class BetweenDirective extends AbstractDirective implements ArgumentBuilderDirectiveInterface, FieldInputDirectiveInterface, FieldArgumentDirectiveInterface
{
    public static function name(): string
    {
        return 'between';
    }

    public static function definition(): string
    {
        return <<<'GRAPHQL'
            directive @between repeatable on ARGUMENT_DEFINITION | INPUT_FIELD_DEFINITION | FIELD_DEFINITION
        GRAPHQL;
    }

    public static function inputsDefinition(): string
    {
        return <<<'GRAPHQL'
            input BetweenInput {
                min: Int!
                max: Int!
            }
        GRAPHQL;
    }

    public function argumentNodes(): array
    {
        return [
            new InputValueDefinitionNode([
                'name' => new NameNode(['value' => 'between']),
                'type' => new NamedTypeNode([
                    'name' => new NameNode(['value' => 'BetweenInput']),
                ]),
                'directives' => new NodeList([]),
                'description' => null,
                'defaultValue' => null,
            ]),
        ];
    }

    public function handleArgumentBuilder(QueryBuilder $queryBuilder, $value): QueryBuilder
    {
        if (!is_array($value) || !isset($value['min'], $value['max'])) {
            return $queryBuilder;
        }

        $alias = current($queryBuilder->getRootAliases());
        $column = $this->getColumn();
        $startParam = ':' . $column . '_start';
        $endParam = ':' . $column . '_end';

        $queryBuilder->andWhere("$alias.$column BETWEEN $startParam AND $endParam")
            ->setParameter($startParam, $value['min'])
            ->setParameter($endParam, $value['max']);

        return $queryBuilder;
    }
}