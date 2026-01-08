<?php

namespace Netmex\Lumina\Schema\AST;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\AST\FieldDefinitionNode;
use Netmex\Lumina\Intent\IntentRegistry;
use Netmex\Lumina\Intent\Intent;

final readonly class TypeVisitor
{
    public function __construct(
        private FieldVisitor   $fieldVisitor,
        private IntentRegistry $intentRegistry
    ) {}

    public function visitType(TypeDefinitionNode $typeNode, array $inputTypes, DocumentNode $document): void
    {
        $typeName = $typeNode->name->value;
        $typeDirectives = $this->fieldVisitor->collectTypeDirectives($typeNode);

        foreach ($typeNode->fields as $fieldNode) {
            if (!$fieldNode instanceof FieldDefinitionNode) continue;

            $intent = new Intent($typeName, $fieldNode->name->value);
            $this->fieldVisitor->applyTypeDirectivesToIntent($intent, $typeDirectives);
            $this->fieldVisitor->visitField($intent, $fieldNode, $inputTypes, $document);

            $this->intentRegistry->add($intent);
        }
    }

    public function getIntentRegistry(): IntentRegistry
    {
        return $this->intentRegistry;
    }
}
