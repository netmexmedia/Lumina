<?php

declare(strict_types=1);

namespace Netmex\Lumina\Schema\AST\New;

use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use Netmex\Lumina\Intent\IntentRegistry;
use Netmex\Lumina\Intent\Intent;

final class TypeVisitor
{
    private DirectiveVisitor $directiveVisitor;
    private IntentRegistry $intentRegistry;

    public function __construct(DirectiveVisitor $directiveVisitor, IntentRegistry $intentRegistry)
    {
        $this->directiveVisitor = $directiveVisitor;
        $this->intentRegistry = $intentRegistry;
    }

    /**
     * Visit a type (object or interface) and process all fields.
     */
    public function visitType($typeNode, array $inputTypes, array $objectTypes, $document): void
    {
        $this->directiveVisitor->setInputTypes($inputTypes);
        $this->directiveVisitor->setObjectTypes($objectTypes);

        $typeName = $typeNode->name->value;

        foreach ($typeNode->fields as $fieldNode) {
            // Root intent only if the field has a directive
            if (!empty($fieldNode->directives)) {
                $intent = new Intent($typeName, $fieldNode->name->value);
                $this->intentRegistry->add($intent);

                // Visit arguments and return type fields recursively
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
