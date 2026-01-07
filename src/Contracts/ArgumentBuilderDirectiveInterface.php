<?php

declare(strict_types=1);

namespace Netmex\Lumina\Contracts;

use Doctrine\ORM\QueryBuilder;

interface ArgumentBuilderDirectiveInterface
{
    public function handleArgumentBuilder(QueryBuilder $queryBuilder, $value);
}