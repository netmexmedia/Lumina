<?php

declare(strict_types=1);

namespace Netmex\Lumina\Directives\Definition;

use Doctrine\ORM\QueryBuilder;
use GraphQL\Type\Definition\ResolveInfo;
use Netmex\Lumina\Context\Context;
use Netmex\Lumina\Contracts\FieldResolverInterface;
use Netmex\Lumina\Contracts\FieldValueInterface;
use Netmex\Lumina\Directives\AbstractDirective;

final class PaginateDirective extends AbstractDirective implements FieldResolverInterface
{
    public static function name(): string
    {
        return 'paginate';
    }

    public static function definition(): string
    {
        return <<<'GRAPHQL'
            directive @paginate(
                model: String,
                resolver: String,,
                limit: Int = 100,
            ) on FIELD_DEFINITION
        GRAPHQL;
    }

    public function resolveField(FieldValueInterface $value, ?QueryBuilder $queryBuilder): callable
    {
        return static function (mixed $root, array $arguments, Context $context, ResolveInfo $info) use ($queryBuilder)
        {
            $page = max(1, $arguments['page'] ?? 1);
            $limit = max(1, $arguments['limit'] ?? 10);
            $offset = ($page - 1) * $limit;

            $queryBuilder->setFirstResult($offset);
            $queryBuilder->setMaxResults($limit);

            return $queryBuilder->getQuery()->getArrayResult();
        };
    }
}