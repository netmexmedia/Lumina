<?php

namespace Netmex\Lumina\Directives;

use Doctrine\ORM\QueryBuilder;

// TODO: QueryBuilder should not be here, need to abstract it out into ResolveInfo later
interface FieldResolverInterface
{
    public function resolveField(FieldValueInterface $value, ?QueryBuilder $queryBuilder): callable;
}