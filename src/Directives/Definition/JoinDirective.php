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
            directive @join(
                target: String,
                type: String
            ) repeatable on ARGUMENT_DEFINITION | INPUT_FIELD_DEFINITION | FIELD_DEFINITION
        GRAPHQL;
    }

    public function handleArgumentBuilder(QueryBuilder $queryBuilder, $value): QueryBuilder
    {
        $relation = $this->nodeName();

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

        $alias = $relation . '_alias';

        $type = strtolower($value['type'] ?? 'inner');

        $joinMethod = match ($type) {
            'left'  => 'leftJoin',
            'inner' => 'innerJoin',
            default => throw new \InvalidArgumentException(
                sprintf('Unsupported join type "%s"', $type)
            ),
        };

        $joins = $queryBuilder->getDQLPart('join');
        $alreadyJoined = isset($joins[$rootAlias]) && array_filter(
                $joins[$rootAlias],
                static fn ($join) => $join->getAlias() === $alias
            );

        if (!$alreadyJoined) {
            $queryBuilder->$joinMethod("$rootAlias.$relation", $alias);
        }

        return $queryBuilder;
    }
}