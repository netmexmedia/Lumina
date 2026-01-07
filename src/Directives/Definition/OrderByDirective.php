<?php

namespace Netmex\Lumina\Directives\Definition;

use Doctrine\ORM\QueryBuilder;
use Netmex\Lumina\Contracts\ArgumentBuilderDirectiveInterface;
use Netmex\Lumina\Directives\AbstractDirective;

class OrderByDirective extends AbstractDirective implements ArgumentBuilderDirectiveInterface
{
    public static function name(): string
    {
        return 'orderBy';
    }

    public static function definition(): string
    {
        return <<<'GRAPHQL'
            directive @orderBy(
                field: String,
                direction: String = "ASC",
            ) repeatable on ARGUMENT_DEFINITION | INPUT_FIELD_DEFINITION | FIELD_DEFINITION
        GRAPHQL;
    }

    public function handleArgumentBuilder(QueryBuilder $queryBuilder, $value): QueryBuilder
    {
        $column = $this->nodeName();
        $param = ':' . $column . '_param';

        $queryBuilder->orderBy($column, $values);

        return $queryBuilder;
    }
}