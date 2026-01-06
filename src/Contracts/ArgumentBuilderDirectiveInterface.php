<?php

namespace Netmex\Lumina\Contracts;

use Doctrine\ORM\QueryBuilder;

interface ArgumentBuilderDirectiveInterface
{
    public function handleArgumentBuilder(QueryBuilder $queryBuilder, $value);
}