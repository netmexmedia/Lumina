<?php

declare(strict_types=1);

namespace Netmex\Lumina\Schema\Source;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use Netmex\Lumina\Contracts\SchemaSourceInterface;

final readonly class SchemaSourceRegistry implements SchemaSourceInterface
{
    /** @param SchemaSourceInterface[] $sources */
    private array $sources;
    private ?Schema $schema;
    private DocumentNode $document;

    public function __construct(array $sources) {
        $this->sources = $sources;
    }

    public function load(): string
    {
        return implode("\n\n", array_map(
            static fn (SchemaSourceInterface $source) => $source->load(),
            $this->sources
        ));
    }

    public function schema(): Schema
    {
        return $this->schema ??= BuildSchema::build($this->load());
    }

    public function document(): DocumentNode
    {
        return $this->document ??= Parser::parse($this->load());
    }
}
