<?php

declare(strict_types=1);

namespace Netmex\Lumina\Paginator;

use Doctrine\ORM\QueryBuilder;

final class Paginator
{
    /**
     * Paginate a Doctrine QueryBuilder.
     *
     * @param QueryBuilder $qb The query builder for the entity
     * @param int $page Page number (1-based)
     * @param int $limit Items per page
     * @param string|null $alias Optional root alias if not automatically detected
     *
     * @return array{
     *     items: array,
     *     paginatorInfo: array{
     *         currentPage: int,
     *         perPage: int,
     *         total: int,
     *         lastPage: int,
     *         hasMorePages: bool
     *     }
     * }
     */
    public static function paginate(QueryBuilder $qb, int $page = 1, int $limit = 10, ?string $alias = null): array
    {
        $alias = $alias ?? current($qb->getRootAliases());
        if (!$alias) {
            throw new \RuntimeException('Cannot detect root alias for pagination.');
        }

        $page = max(1, $page);
        $limit = max(1, $limit);
        $offset = ($page - 1) * $limit;

        $total = (int) (clone $qb)
            ->select("COUNT($alias.id)")
            ->setFirstResult(null)
            ->setMaxResults(null)
            ->getQuery()
            ->getSingleScalarResult();

        // --- Get page items ---
        $items = $qb
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();

        $lastPage = $limit > 0 ? (int) ceil($total / $limit) : 1;
        $hasMorePages = $page < $lastPage;

        return [
            'items' => $items,
            'paginatorInfo' => [
                'currentPage' => $page,
                'perPage' => $limit,
                'total' => $total,
                'lastPage' => $lastPage,
                'hasMorePages' => $hasMorePages,
            ],
        ];
    }
}
