<?php

namespace Netmex\Lumina\Schema\AST\New;

use Netmex\Lumina\Intent\Intent;

final class ReturnTypeVisitor
{
    public function visitReturnType(Intent $parentIntent, string $typeName, array $objectTypes, string $path = ''): void
    {
        $typeNode = $objectTypes[$typeName] ?? null;
        if (!$typeNode) return;

        foreach ($typeNode->fields as $fieldNode) {
            $fieldName = $fieldNode->name->value;
            $childIntent = new Intent($typeName, $fieldName);
            $childIntent->setParent($parentIntent);
            $parentIntent->addChild($childIntent);

            $newPath = $path === '' ? $fieldName : $path . '.' . $fieldName;

            $nestedType = $this->getNamedType($fieldNode->type);
            if (isset($objectTypes[$nestedType])) {
                $this->visitReturnType($childIntent, $nestedType, $objectTypes, $newPath);
            }
        }
    }

    private function getNamedType($typeNode): string
    {
        if (property_exists($typeNode, 'name') && $typeNode->name) {
            return $typeNode->name->value;
        }
        if (property_exists($typeNode, 'type') && $typeNode->type) {
            return $this->getNamedType($typeNode->type);
        }
        throw new \RuntimeException('Cannot resolve named type');
    }
}
