<?php

namespace Netmex\Lumina\Schema\Helpers;

final class ASTTypeHelper
{
    public static function getNamedType($typeNode): string
    {
        if (property_exists($typeNode, 'name') && $typeNode->name) {
            return $typeNode->name->value;
        }
        if (property_exists($typeNode, 'type') && $typeNode->type) {
            return self::getNamedType($typeNode->type);
        }
        throw new \RuntimeException('Cannot resolve named type');
    }
}
