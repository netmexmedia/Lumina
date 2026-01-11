<?php

declare(strict_types=1);

namespace Netmex\Lumina\Directives\Definition;

use Doctrine\ORM\QueryBuilder;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Language\AST\NameNode;
use GraphQL\Language\AST\NodeList;
use GraphQL\Language\AST\NonNullTypeNode;
use GraphQL\Type\Definition\ResolveInfo;
use Netmex\Lumina\Context\Context;
use Netmex\Lumina\Contracts\FieldArgumentDirectiveInterface;
use Netmex\Lumina\Contracts\FieldResolverInterface;
use Netmex\Lumina\Contracts\FieldValueInterface;
use Netmex\Lumina\Directives\AbstractDirective;

final class FindDirective extends AbstractDirective implements FieldResolverInterface, FieldArgumentDirectiveInterface
{
    public static function name(): string
    {
        return 'find';
    }

    public static function definition(): string
    {
        return <<<'GRAPHQL'
            directive @find(
                model: String,
                resolver: String
            ) on FIELD_DEFINITION
        GRAPHQL;
    }

    public function argumentNodes(): array
    {
        return [
            new InputValueDefinitionNode([
                'name' => new NameNode(['value' => 'id']),
                'type' => new NonNullTypeNode([
                    'type' => new NamedTypeNode([
                        'name' => new NameNode(['value' => 'ID']),
                    ]),
                ]),
                'directives' => new NodeList([]),
                'description' => null,
                'defaultValue' => null,
            ])
        ];
    }

    public function resolveField(FieldValueInterface $value, ?QueryBuilder $queryBuilder): callable
    {
        return static function (mixed $root, array $args, Context $context, ResolveInfo $info) use ($queryBuilder) {
            $alias = current($queryBuilder->getRootAliases());
            $id = $args['id'] ?? null;

            return $queryBuilder
                ->where("$alias.id = :id")
                ->setParameter('id', $id)
                ->getQuery()
                ->getOneOrNullResult();
        };
    }
}
