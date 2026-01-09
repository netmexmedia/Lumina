<?php

declare(strict_types=1);

namespace Netmex\Lumina\Schema\AST;

use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\DocumentNode;
use Netmex\Lumina\Intent\IntentRegistry;
use Netmex\Lumina\Intent\Intent;

final class TypeVisitor
{
    private FieldVisitor $fieldVisitor;
    private IntentRegistry $intentRegistry;

    public function __construct(FieldVisitor $fieldVisitor, IntentRegistry $intentRegistry)
    {
        $this->fieldVisitor = $fieldVisitor;
        $this->intentRegistry = $intentRegistry;
    }

    public function visitType(TypeDefinitionNode $typeNode, array $inputTypes, DocumentNode $document): void
    {
        $typeName = $typeNode->name->value;

        $typeDirectives = $this->fieldVisitor->collectTypeDirectives($typeNode);

        foreach ($typeNode->fields as $fieldNode) {
            if (!$fieldNode instanceof FieldDefinitionNode) {
                continue;
            }

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
