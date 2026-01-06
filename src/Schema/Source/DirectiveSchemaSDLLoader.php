<?php

namespace Netmex\Lumina\Schema\Source;

use Netmex\Lumina\Directives\Registery\DirectiveRegistry;

final readonly class DirectiveSchemaSDLLoader implements SchemaSDLLoaderInterface
{
    public function __construct(
        private DirectiveRegistry $registry
    ) {}

    public function load(): string
    {
        return $this->registry->definitionsSDL();
    }
}
