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
                columns: [String!]!
                exact: Boolean
            ) repeatable on ARGUMENT_DEFINITION | INPUT_FIELD_DEFINITION | FIELD_DEFINITION
        GRAPHQL;
    }

    public function handleArgumentBuilder(QueryBuilder $queryBuilder, $value): QueryBuilder
    {
        if ($value === null) {
            return $queryBuilder;
        }

        $alias = current($queryBuilder->getRootAliases());
        $columns = $this->getArgument('columns') ?? [$this->nodeName()];
        $exact = $this->getArgument('exact') ?? true;

        $orX = $queryBuilder->expr()->orX();

        foreach ($columns as $col) {
            $paramName = str_replace('.', '_', $col) . '_param';

            if ($exact) {
                $orX->add("$alias.$col = :$paramName");
                $queryBuilder->setParameter($paramName, $value);
                continue;
            }

            $orX->add("$alias.$col LIKE :$paramName");
            $queryBuilder->setParameter($paramName, "%$value%");
        }

        $queryBuilder->andWhere($orX);

        return $queryBuilder;
    }
}