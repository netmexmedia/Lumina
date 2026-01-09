<?php

namespace Netmex\Lumina\Directives\Definition;

use Doctrine\ORM\QueryBuilder;
use Netmex\Lumina\Contracts\ArgumentBuilderDirectiveInterface;
use Netmex\Lumina\Directives\AbstractDirective;

class HasManyDirective extends AbstractDirective implements ArgumentBuilderDirectiveInterface
{
    public static function name(): string
    {
        return 'hasMany';
    }

    public static function definition(): string
    {
        return <<<'GRAPHQL'
            directive @hasMany(
                target: String,
            ) repeatable on ARGUMENT_DEFINITION | INPUT_FIELD_DEFINITION | FIELD_DEFINITION
        GRAPHQL;
    }

    public function handleArgumentBuilder(QueryBuilder $queryBuilder, $value): QueryBuilder
    {
        $relation = $this->getColumn();

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $rootEntity = $queryBuilder->getRootEntities()[0];

        $em = $queryBuilder->getEntityManager();
        $metadata = $em->getClassMetadata($rootEntity);

        if (!$metadata->hasAssociation($relation)) {
            throw new \InvalidArgumentException(sprintf(
                'Relation "%s" does not exist on %s',
                $relation,
                $rootEntity
            ));
        }

        $alias = $relation . '_alias';

        $queryBuilder->innerJoin("$rootAlias.$relation", $alias)
            ->addSelect($alias)
            ->distinct();

        return $queryBuilder;
    }
}