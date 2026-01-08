<?php

declare(strict_types=1);

namespace Netmex\Lumina\Schema\AST;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use Netmex\Lumina\Intent\IntentRegistry;
use Netmex\Lumina\Schema\Source\SchemaSourceRegistry;

final class IntentCompiler
{
    private array $inputTypes = [];

    public function __construct(
        private readonly SchemaSourceRegistry $schemaSource,
        private readonly TypeVisitor          $typeVisitor
    ) {}

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
