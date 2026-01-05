<?php

namespace Netmex\Lumina;

use Netmex\Lumina\Schema\Source\SchemaSDLLoaderInterface;

final readonly class DirectiveSchemaSDLLoader implements SchemaSDLLoaderInterface
{
    /**
     * @param iterable<SchemaSDLContributorInterface> $contributors
     */
    public function __construct(
        private iterable $contributors
    ) {}

    public function load(): string
    {
        $definitions = [];

        foreach ($this->contributors as $contributor) {
            $definitions[] = $contributor->definitions();
        }

        return implode("\n\n", $definitions);
    }
}