<?php

namespace Netmex\Lumina\Schema;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use Netmex\Lumina\Intent\IntentRegistry;
use Netmex\Lumina\Schema\AST\TypeVisitor;
use Netmex\Lumina\Schema\Source\SchemaSourceRegistry;

final class SchemaVisitor
{
    private SchemaSourceRegistry $schemaSource;
    private TypeVisitor $typeVisitor;
    private array $inputTypes = [];
    private array $objectTypes = [];

    public function __construct(SchemaSourceRegistry $schemaSource, TypeVisitor $typeVisitor)
    {
        $this->schemaSource = $schemaSource;
        $this->typeVisitor = $typeVisitor;
    }

    public function visit(): IntentRegistry
    {
        $document = $this->schemaSource->getDocument();
        $this->indexTypes($document);

        foreach ($document->definitions as $typeDef) {
            if ($this->isVisitableType($typeDef)) {
                $this->typeVisitor->visitType($typeDef, $this->inputTypes, $this->objectTypes, $document);
            }
        }

        $this->schemaSource->setDocument($document);

        return $this->typeVisitor->getIntentRegistry();
    }

    private function indexTypes(DocumentNode $document): void
    {
        foreach ($document->definitions as $def) {
            // Input object types
            if ($def instanceof InputObjectTypeDefinitionNode) {
                $this->inputTypes[$def->name->value] = $def;
            }

            // Object types
            if ($def instanceof ObjectTypeDefinitionNode) {
                $this->objectTypes[$def->name->value] = $def;
            }
        }
    }

    private function isVisitableType($typeDef): bool
    {
        return $typeDef instanceof ObjectTypeDefinitionNode
            || $typeDef instanceof InterfaceTypeDefinitionNode;
    }
}
