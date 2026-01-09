<?php

declare(strict_types=1);

namespace Netmex\Lumina\Schema;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use Netmex\Lumina\Intent\IntentRegistry;
use Netmex\Lumina\Schema\AST\TypeVisitor;
use Netmex\Lumina\Schema\Source\SchemaSourceRegistry;

final class SchemaCompiler
{
    private SchemaSourceRegistry $schemaSource;
    private TypeVisitor $typeVisitor;
    private array $inputTypes = [];

    public function __construct(SchemaSourceRegistry $schemaSource, TypeVisitor $typeVisitor)
    {
        $this->schemaSource = $schemaSource;
        $this->typeVisitor = $typeVisitor;
    }

    public function compile(): IntentRegistry
    {
        $document = $this->schemaSource->getDocument();

        if (!$document) {
            throw new \RuntimeException('Schema document is missing from schema source.');
        }

        $this->indexInputTypes($document);
        $this->traverseDocument($document);

        $this->schemaSource->setDocument($document);

        return $this->typeVisitor->getIntentRegistry();
    }

    private function indexInputTypes(DocumentNode $document): void
    {
        foreach ($document->definitions as $def) {
            if ($def instanceof InputObjectTypeDefinitionNode) {
                $this->inputTypes[$def->name->value] = $def;
            }
        }
    }

    private function traverseDocument(DocumentNode $document): void
    {
        foreach ($document->definitions as $def) {
            if ($def instanceof TypeDefinitionNode) {
                $this->typeVisitor->visitType($def, $this->inputTypes, $document);
            }
        }
    }
}
