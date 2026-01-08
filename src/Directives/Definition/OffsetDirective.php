<?php

namespace Netmex\Lumina\Directives\Definition;

use Doctrine\ORM\QueryBuilder;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Language\AST\NameNode;
use GraphQL\Language\AST\NodeList;
use GraphQL\Language\AST\NonNullTypeNode;
use Netmex\Lumina\Contracts\ArgumentBuilderDirectiveInterface;
use Netmex\Lumina\Contracts\FieldArgumentDirectiveInterface;
use Netmex\Lumina\Directives\AbstractDirective;

class OffsetDirective extends AbstractDirective implements ArgumentBuilderDirectiveInterface, FieldArgumentDirectiveInterface
{
    public static function name(): string
    {
        return 'offset';
    }

    public static function definition(): string
    {
        return <<<'GRAPHQL'
            directive @offset(
                value: Int,
            ) repeatable on ARGUMENT_DEFINITION | INPUT_FIELD_DEFINITION | FIELD_DEFINITION
        GRAPHQL;
    }

    public function argumentNodes(): array
    {
        return [
            new InputValueDefinitionNode([
                'name' => new NameNode(['value' => 'offset']),
                'type' => new NonNullTypeNode([
                    'type' => new NamedTypeNode([
                        'name' => new NameNode(['value' => 'Int']),
                    ]),
                ]),
                'directives' => new NodeList([]),
                'description' => null,
                'defaultValue' => null,
            ])
        ];
    }

    public function handleArgumentBuilder(QueryBuilder $queryBuilder, $value): QueryBuilder
    {
        $queryBuilder->setFirstResult($value);

        return $queryBuilder;
    }
}