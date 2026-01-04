<?php

namespace Netmex\Lumina\Schema;

use Netmex\Lumina\Schema\Directives\DirectiveRegistry;
use Netmex\Lumina\SchemaSDLContributorInterface;

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
