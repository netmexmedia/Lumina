<?php

namespace Netmex\Lumina;

use GraphQL\Type\Definition\ResolveInfo;

class DefaultFieldResolver
{
    public function __invoke($source, array $args, $context, ResolveInfo $info) {
        $field = $info->fieldName;
        dd($source, $args, $context, $info);

        if (is_array($source)) {
            return $source[$field] ?? null;
        }

        if (is_object($source)) {
            $getter = 'get' . ucfirst($field);

            if (method_exists($source, $getter)) {
                return $source->$getter();
            }

            if (property_exists($source, $field)) {
                return $source->$field;
            }
        }

        return null;
    }
}
