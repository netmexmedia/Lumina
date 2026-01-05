<?php

namespace Netmex\Lumina\Schema\Source;

use Netmex\Lumina\SchemaSource;

final readonly class FileSchemaSource implements SchemaSource
{
    public function __construct(
        private string $path
    ) {}

    public function load(): string
    {
        $chunks = [];

        foreach (glob($this->path . '/*.graphql') ?: [] as $file) {
            $chunks[] = file_get_contents($file);
        }

        return implode("\n", $chunks);
    }
}