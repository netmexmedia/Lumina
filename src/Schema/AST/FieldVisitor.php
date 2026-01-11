<?php

namespace Netmex\Lumina\Schema\AST;

use Netmex\Lumina\Intent\Intent;

final class FieldVisitor
{
    private ArgumentVisitor $argumentVisitor;
    private ReturnTypeVisitor $returnTypeVisitor;

    public function __construct(ArgumentVisitor $argumentVisitor, ReturnTypeVisitor $returnTypeVisitor)
    {
        $this->argumentVisitor = $argumentVisitor;
        $this->returnTypeVisitor = $returnTypeVisitor;
    }

    public function visitField(Intent $intent, $fieldNode, array $inputTypes, array $objectTypes): void
    {
        // Visit arguments (input objects)
        foreach ($fieldNode->arguments ?? [] as $argNode) {
            $this->argumentVisitor->visitArgument($intent, $argNode, $fieldNode, $inputTypes, $objectTypes);
        }

        // Visit return type (object types)
        $returnTypeName = $this->getNamedType($fieldNode->type);
        $this->returnTypeVisitor->visitReturnType($intent, $returnTypeName, $objectTypes);
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
