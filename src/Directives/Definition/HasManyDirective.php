<?php

namespace Netmex\Lumina\Directives\Definition;

use Doctrine\ORM\QueryBuilder;
use Netmex\Lumina\Contracts\FieldResolverInterface;
use Netmex\Lumina\Contracts\FieldValueInterface;
use Netmex\Lumina\Directives\AbstractDirective;

class HasManyDirective extends AbstractDirective implements FieldResolverInterface
{
    public static function name(): string
    {
        return 'hasMany';
    }

    public static function definition(): string
    {
        return <<<'GRAPHQL'
            directive @hasMany(
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

        return static function ($root, array $arguments, $context, $info) use ($fqcn) {
            $em = $context->entityManager;

            $qb = $em->getRepository($fqcn)
                ->createQueryBuilder('c')
                ->where('c.test = :parentId')
                ->setParameter('parentId', $root['id']);

            if (!empty($arguments['limit'])) {
                $qb->setMaxResults((int) $arguments['limit']);
            }

            return $qb->getQuery()->getArrayResult();
        };
    }
}
