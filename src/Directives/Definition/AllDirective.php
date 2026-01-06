<?php

namespace Netmex\Lumina\Directives\Definition;

use Doctrine\ORM\QueryBuilder;
use GraphQL\Type\Definition\ResolveInfo;
use Netmex\Lumina\Context\Context;
use Netmex\Lumina\Contracts\FieldResolverInterface;
use Netmex\Lumina\Contracts\FieldValueInterface;
use Netmex\Lumina\Directives\AbstractDirective;

final class AllDirective extends AbstractDirective implements FieldResolverInterface
{
    public static function name(): string
    {
        return 'all';
    }

    public static function definition(): string
    {
        return <<<'GRAPHQL'
            directive @all(
                model: String,
                resolver: String
            ) on FIELD_DEFINITION
        GRAPHQL;
    }

    public function resolveField(FieldValueInterface $value, ?QueryBuilder $queryBuilder): callable
    {
        return static function (mixed $root, array $arguments, Context $context, ResolveInfo $info) use ($queryBuilder)
        {
            return $queryBuilder->getQuery()->getArrayResult();
        };
    }
}