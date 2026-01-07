<?php

namespace Netmex\Lumina\Schema\Source;

use Netmex\Lumina\Contracts\SchemaSourceInterface;

final readonly class SchemaSourceRegistry implements SchemaSourceInterface
{
    /** @param SchemaSourceInterface[] $sources */
    private array $sources;

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
}
