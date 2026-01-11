<?php

namespace Netmex\Lumina\Directives\Definition;

use Doctrine\ORM\QueryBuilder;
use Netmex\Lumina\Contracts\FieldResolverInterface;
use Netmex\Lumina\Contracts\FieldValueInterface;
use Netmex\Lumina\Directives\AbstractDirective;

class JoinDirective extends AbstractDirective implements FieldResolverInterface
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
                type: JoinType = INNER
            ) repeatable on FIELD_DEFINITION
        GRAPHQL;
    }

    public function resolveField(FieldValueInterface $value, ?QueryBuilder $queryBuilder): callable
    {
        $shortName = $this->modelClass();
        $fqcn      = $this->resolveEntityFQCN($shortName);

        if (!$fqcn) {
            throw new \RuntimeException("Cannot resolve entity FQCN for $shortName");
        }

        return function ($root, array $arguments, $context, $info) use ($fqcn) {
            $em = $context->entityManager;

            $qb = $em->getRepository($fqcn)
                ->createQueryBuilder('c')
                ->where('c.test = :parentId')
                ->setParameter('parentId', $root['id']);



            return $qb->getQuery()->getArrayResult();
        };
    }
}
