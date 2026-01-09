<?php

namespace Netmex\Lumina\Directives\Definition;

use Doctrine\ORM\QueryBuilder;
use Netmex\Lumina\Contracts\ArgumentBuilderDirectiveInterface;
use Netmex\Lumina\Directives\AbstractDirective;

class JoinDirective extends AbstractDirective implements ArgumentBuilderDirectiveInterface
{
    public static function name(): string
    {
        return 'join';
    }

    public static function definition(): string
    {
        return <<<'GRAPHQL'
            enum JoinType {
              INNER
              LEFT
            }

            directive @join(
                target: String,
                type: JoinType
            ) repeatable on ARGUMENT_DEFINITION | INPUT_FIELD_DEFINITION | FIELD_DEFINITION
        GRAPHQL;
    }

    public function handleArgumentBuilder(QueryBuilder $queryBuilder, $value): QueryBuilder
    {
        $relation = $this->getColumn();

        $rootAlias  = $queryBuilder->getRootAliases()[0];
        $rootEntity = $queryBuilder->getRootEntities()[0];

        $metadata = $queryBuilder->getEntityManager()->getClassMetadata($rootEntity);

        if (!$metadata->hasAssociation($relation)) {
            throw new \InvalidArgumentException(sprintf(
                'Relation "%s" does not exist on %s',
                $relation,
                $rootEntity
            ));
        }

        // Use a safe alias
        $alias = $relation . '_alias';

        $type = $this->getArgument('type', 'INNER');

        $joinMethod = match ($type) {
            'LEFT'  => 'leftJoin',
            'INNER' => 'innerJoin',

            default => throw new \InvalidArgumentException(
                sprintf('Unsupported join type "%s"', $type)
            ),
        };

        // Avoid duplicate joins
        $joins = $queryBuilder->getDQLPart('join');
        $alreadyJoined = isset($joins[$rootAlias]) && array_filter(
                $joins[$rootAlias],
                static fn ($join) => $join->getAlias() === $alias
            );

        if (!$alreadyJoined) {
            $queryBuilder->$joinMethod("$rootAlias.$relation", $alias)
                ->addSelect($alias)
                ->distinct();
        }

        return $queryBuilder;
    }
}