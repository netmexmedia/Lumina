<?php

namespace Netmex\Lumina\Schema\AST;

use Netmex\Lumina\Intent\Intent;

final class ArgumentVisitor
{
    public function visitArgument(Intent $parentIntent, $argNode, $fieldNode, array $inputTypes, array $objectTypes, string $path = ''): void
    {
        $argName = $argNode->name->value;
        $argIntent = new Intent($parentIntent->typeName, $argName);
        $argIntent->setParent($parentIntent);
        $parentIntent->addChild($argIntent);

        $newPath = $path === '' ? $argName : $path . '.' . $argName;

        // Recurse if input object
        $typeName = $this->getNamedType($argNode->type);
        if (isset($inputTypes[$typeName])) {
            foreach ($inputTypes[$typeName]->fields as $nestedArg) {
                $this->visitArgument($argIntent, $nestedArg, $fieldNode, $inputTypes, $objectTypes, $newPath);
            }
        }

        // Optionally, also recurse into return type if needed
        if (isset($objectTypes[$typeName])) {
            $returnVisitor = new ReturnTypeVisitor();
            $returnVisitor->visitReturnType($argIntent, $typeName, $newPath, $objectTypes);
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
