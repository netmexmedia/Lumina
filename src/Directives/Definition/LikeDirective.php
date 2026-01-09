<?php

namespace Netmex\Lumina\Directives\Definition;

use Doctrine\ORM\QueryBuilder;
use Netmex\Lumina\Contracts\ArgumentBuilderDirectiveInterface;
use Netmex\Lumina\Directives\AbstractDirective;

final class LikeDirective extends AbstractDirective implements ArgumentBuilderDirectiveInterface
{
    public static function name(): string
    {
        return 'like';
    }

    public static function definition(): string
    {
        return <<<'GRAPHQL'
            directive @like(
                on: String
            ) repeatable on ARGUMENT_DEFINITION | INPUT_FIELD_DEFINITION | FIELD_DEFINITION
        GRAPHQL;
    }

    public function handleArgumentBuilder(QueryBuilder $queryBuilder, $value): QueryBuilder
    {
        if ($value === null) {
            return $queryBuilder;
        }

        $column = $this->getColumn();
        $param = ':' . $column."_param";

        $alias = current($queryBuilder->getRootAliases());

        $queryBuilder->andWhere("$alias.$column LIKE $param")
            ->setParameter($param, "%$value%");

        return $queryBuilder;
    }
}