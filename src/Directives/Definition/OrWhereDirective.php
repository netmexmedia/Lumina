<?php

declare(strict_types=1);

namespace Netmex\Lumina\Directives\Definition;

use Doctrine\ORM\QueryBuilder;
use Netmex\Lumina\Contracts\ArgumentBuilderDirectiveInterface;
use Netmex\Lumina\Directives\AbstractDirective;

final class OrWhereDirective extends AbstractDirective implements ArgumentBuilderDirectiveInterface
{
    public static function name(): string
    {
        return 'orWhere';
    }

    public static function definition(): string
    {
        return <<<'GRAPHQL'
            directive @orWhere(
                on: String
            ) repeatable on ARGUMENT_DEFINITION | INPUT_FIELD_DEFINITION | FIELD_DEFINITION
        GRAPHQL;
    }

    public function handleArgumentBuilder(QueryBuilder $queryBuilder, $value): QueryBuilder
    {
        if ($value === null) {
            return $queryBuilder;
        }

        $column = $this->nodeName();
        $param = ':' . $column."_param";

        $queryBuilder->orWhere("e.$column = $param")
            ->setParameter($param, $value);

        return $queryBuilder;
    }
}