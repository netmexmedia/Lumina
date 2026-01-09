<?php

declare(strict_types=1);

namespace Netmex\Lumina\Schema\AST;

use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\DocumentNode;
use Netmex\Lumina\Contracts\IntentFactoryInterface;
use Netmex\Lumina\Intent\IntentRegistry;

final class TypeVisitor
{
    private FieldVisitor $fieldVisitor;
    private IntentRegistry $intentRegistry;
    private IntentFactoryInterface $intentFactory;

    public function __construct(FieldVisitor $fieldVisitor, IntentRegistry $intentRegistry, IntentFactoryInterface $intentFactory)
    {
        $this->fieldVisitor = $fieldVisitor;
        $this->intentRegistry = $intentRegistry;
        $this->intentFactory = $intentFactory;
    }

    public function visitType(TypeDefinitionNode $typeNode, array $inputTypes, DocumentNode $document): void
    {
        if (!$typeNode instanceof ObjectTypeDefinitionNode && !$typeNode instanceof InterfaceTypeDefinitionNode && !$typeNode instanceof InputObjectTypeDefinitionNode) {
            return;
        }

        $typeName = $typeNode->name->value;

        $objectTypes = $this->collectObjectTypes($document);
        $this->fieldVisitor->getArgumentVisitor()->setObjectTypes($objectTypes);

        $typeDirectives = $this->fieldVisitor->collectTypeDirectives($typeNode);

        foreach ($typeNode->fields as $fieldNode) {
            if (!$fieldNode instanceof FieldDefinitionNode) {
                continue;
            }

            $intent = $this->intentFactory->create($typeName, $fieldNode->name->value, $typeDirectives);

            $this->fieldVisitor->applyTypeDirectivesToIntent($intent, $typeDirectives);
            $this->fieldVisitor->visitField($intent, $fieldNode, $inputTypes, $document);

            $this->intentRegistry->add($intent);
        }
    }

    private function collectObjectTypes(DocumentNode $document): array
    {
        $objectTypes = [];
        foreach ($document->definitions as $def) {
            if ($def instanceof ObjectTypeDefinitionNode) {
                $objectTypes[$def->name->value] = $def;
            }
        }

        return $objectTypes;
    }

    public function getIntentRegistry(): IntentRegistry
    {
        return $this->intentRegistry;
    }
}
