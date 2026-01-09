<?php

declare(strict_types=1);

namespace Netmex\Lumina\Directives\Definition;

use Doctrine\ORM\QueryBuilder;
use Netmex\Lumina\Contracts\ArgumentBuilderDirectiveInterface;
use Netmex\Lumina\Directives\AbstractDirective;

final class WhereDirective extends AbstractDirective implements ArgumentBuilderDirectiveInterface
{
    public static function name(): string
    {
        return 'where';
    }

    public static function definition(): string
    {
        return <<<'GRAPHQL'
            directive @where(
                on: String
            ) repeatable on ARGUMENT_DEFINITION | INPUT_FIELD_DEFINITION | FIELD_DEFINITION
        GRAPHQL;
    }

    public function handleArgumentBuilder(QueryBuilder $queryBuilder, $value): QueryBuilder
    {
        if ($value === null) {
            return $queryBuilder;
        }
        $alias = current($queryBuilder->getRootAliases());
        $column = $this->nodeName();
        $param = ':' . $column."_param";

        $queryBuilder->andWhere("$alias.$column = $param")
            ->setParameter($param, $value);

        return $queryBuilder;
    }
}