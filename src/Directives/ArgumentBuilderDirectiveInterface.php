<?php

namespace Netmex\Lumina\Directives;

use Doctrine\ORM\QueryBuilder;

interface ArgumentBuilderDirectiveInterface
{
    public function handleArgumentBuilder(QueryBuilder $queryBuilder, $value);
}