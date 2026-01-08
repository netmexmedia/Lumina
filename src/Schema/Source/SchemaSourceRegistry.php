<?php

declare(strict_types=1);

namespace Netmex\Lumina\Schema\Source;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use Netmex\Lumina\Contracts\SchemaSourceInterface;

final class SchemaSourceRegistry implements SchemaSourceInterface
{
    /** @param SchemaSourceInterface[] $sources */
    private array $sources;
    private ?Schema $schema = null;
    private ?DocumentNode $document = null;

    public function __construct(array $sources) {
        $this->sources = $sources;
    }

    public function getSchema(): ?Schema
    {
        return $this->schema;
    }

    public function getDocument(): ?DocumentNode
    {
        return $this->document;
    }

    public function setDocument(DocumentNode $document): void
    {
        $this->document = $document;
        $this->schema = BuildSchema::buildAST($document);
    }

    public function buildDocumentFromSdl(): DocumentNode
    {
        $this->document = Parser::parse($this->load());
        return $this->document;
    }

    public function load(): string
    {
        return implode("\n\n", array_map(
            static fn (SchemaSourceInterface $source) => $source->load(),
            $this->sources
        ));
    }
}
