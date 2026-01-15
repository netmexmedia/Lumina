<?php

declare(strict_types=1);

namespace Netmex\Lumina\Schema\AST;

use Netmex\Lumina\Intent\Intent;
use Netmex\Lumina\Intent\IntentRegistry;
use Netmex\Lumina\Schema\Directive\DirectiveTypeRegistryVisitor;

final class TypeVisitor
{
    private DirectiveVisitor $directiveVisitor;
    private IntentRegistry $intentRegistry;

    public function __construct(DirectiveVisitor $directiveVisitor, IntentRegistry $intentRegistry)
    {
        $this->directiveVisitor = $directiveVisitor;
        $this->intentRegistry = $intentRegistry;
    }

    public function visitType($typeNode, array $inputTypes, array $objectTypes, $document): void
    {
        $this->directiveVisitor->types->setInputTypes($inputTypes);
        $this->directiveVisitor->types->setObjectTypes($objectTypes);

        $typeName = $typeNode->name->value;

        foreach ($typeNode->fields as $fieldNode) {
            if (!empty($fieldNode->directives)) {
                $intent = new Intent($typeName, $fieldNode->name->value);
                $this->intentRegistry->add($intent);

                if (property_exists($fieldNode, 'arguments') && !empty($fieldNode->arguments)) {
                    $this->directiveVisitor->visitArguments($intent, $fieldNode->arguments);
                }

                $this->directiveVisitor->visitReturnType($intent, [$fieldNode], $document);
            }
        }
    }

    public function getIntentRegistry(): IntentRegistry
    {
        return $this->intentRegistry;
    }
}
