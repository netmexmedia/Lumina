<?php

namespace Netmex\Lumina\Directives\Definition;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Netmex\Lumina\Contracts\FieldResolverInterface;
use Netmex\Lumina\Contracts\FieldValueInterface;
use Netmex\Lumina\Directives\AbstractDirective;

class BelongsToDirective extends AbstractDirective implements FieldResolverInterface
{
    public static function name(): string
    {
        return 'belongsTo';
    }

    public static function definition(): string
    {
        return <<<'GRAPHQL'
            directive @belongsTo(
                column: String,
            ) repeatable on FIELD_DEFINITION
        GRAPHQL;
    }

    public function resolveField(FieldValueInterface $value, ?QueryBuilder $queryBuilder): callable
    {
        return static function ($root, array $arguments, $context, $info) use ($queryBuilder) {
            if (!$queryBuilder) {
                throw new \RuntimeException('QueryBuilder not provided to BelongsToDirective');
            }

            $foreignKey = 'id';

            if (!isset($root[$foreignKey])) {
                return null;
            }

            $queryBuilder
                ->andWhere('root.id = :parentId')
                ->setParameter('parentId', $root[$foreignKey]);

            return $queryBuilder->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);
        };
    }
}
