<?php

namespace Netmex\Lumina\Schema\Source;

use Netmex\Lumina\Contracts\SchemaSourceInterface;
use Netmex\Lumina\Directives\Registry\DirectiveRegistry;

final readonly class DirectiveSchemaSource implements SchemaSourceInterface
{
    private DirectiveRegistry $directives;

    public function __construct(DirectiveRegistry $directives) {
        $this->directives = $directives;
    }

    public function load(): string
    {
        $chunks = [];

        foreach ($this->directives->all() as $name => $className) {
            $chunks[] = $className::definition();
        }

        return implode("\n\n", $chunks);
    }
}
