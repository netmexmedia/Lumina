<?php

namespace Netmex\Lumina\Directives\Definition;

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
        $shortName = $this->modelClass();
        $fqcn = $this->resolveEntityFQCN($shortName);

        if (!$fqcn) {
            throw new \RuntimeException("Cannot resolve entity FQCN for $shortName");
        }

        return function ($root, array $arguments, $context, $info) use ($fqcn) {
            $em = $context->entityManager;

            $column = $this->getColumn() ?? 'id';
            $alias  = 'b_alias';

            $qb = $em->getRepository($fqcn)->createQueryBuilder('b')
                ->where("b.$column = :parentId")
                ->setParameter('parentId', $root[$column]);

            return $qb->getQuery()->getArrayResult();
        };
    }
}
