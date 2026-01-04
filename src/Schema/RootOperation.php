<?php

namespace Netmex\Lumina\Schema;

enum RootOperation: string
{
    case QUERY = 'Query';
    case MUTATION = 'Mutation';
    case SUBSCRIPTION = 'Subscription';

    public static function isRootType(string $typeName): bool
    {
        return self::tryFrom($typeName) !== null;
    }
}
