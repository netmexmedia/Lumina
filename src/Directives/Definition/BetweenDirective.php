<?php

namespace Netmex\Lumina\Directives\Definition;

use Doctrine\ORM\QueryBuilder;
use Netmex\Lumina\Contracts\ArgumentBuilderDirectiveInterface;
use Netmex\Lumina\Directives\AbstractDirective;

class BetweenDirective extends AbstractDirective implements ArgumentBuilderDirectiveInterface
{
    public static function name(): string
    {
        return 'between';
    }

    public static function definition(): string
    {
        return <<<'GRAPHQL'
            directive @between(
                field: String,
                min: Int,
                max: Int
            ) repeatable on ARGUMENT_DEFINITION | INPUT_FIELD_DEFINITION | FIELD_DEFINITION
        GRAPHQL;
    }

    public function handleArgumentBuilder(QueryBuilder $queryBuilder, $value): QueryBuilder
    {
        $column = $this->nodeName();
        // TODO Should be gotten from directive arguments
        $startParam = ':' . $column . '_start';
        $endParam = ':' . $column . '_end';

        $queryBuilder->andWhere("e.$column BETWEEN $startParam AND $endParam")
            ->setParameter($startParam, $start)
            ->setParameter($endParam, $end);

        return $queryBuilder;
    }
}