<?php

namespace Netmex\Lumina\Query;

use Netmex\Lumina\QueryExecutorInterface;

final class DebugQueryExecutor implements QueryExecutorInterface
{
    public function execute(QueryIntent $intent): array
    {
        return [
            'root' => $intent->getRoot(),
            'mode' => $intent->getMode(),
            'filters' => $intent->getFilters(),
        ];
    }
}