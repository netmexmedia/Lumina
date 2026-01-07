<?php

namespace Netmex\Lumina\Directives\Definition;

use Doctrine\ORM\QueryBuilder;
use Netmex\Lumina\Contracts\ArgumentBuilderDirectiveInterface;
use Netmex\Lumina\Directives\AbstractDirective;

class InDirective extends AbstractDirective implements ArgumentBuilderDirectiveInterface
{
    public static function name(): string
    {
        return 'in';
    }

    public static function definition(): string
    {
        return <<<'GRAPHQL'
            directive @in(
                fied: String,
                values: [String!],
                max: Int
            ) repeatable on ARGUMENT_DEFINITION | INPUT_FIELD_DEFINITION | FIELD_DEFINITION
        GRAPHQL;
    }

    public function handleArgumentBuilder(QueryBuilder $queryBuilder, $value): QueryBuilder
    {
        $column = $this->nodeName();
        $param = ':' . $column . '_param';

        $queryBuilder->andWhere("e.$column IN ($param)")
            ->setParameter($param, $value);

        return $queryBuilder;
    }
}