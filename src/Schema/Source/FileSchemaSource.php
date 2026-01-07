<?php

declare(strict_types=1);

namespace Netmex\Lumina\Schema\Source;

use Netmex\Lumina\Contracts\SchemaSourceInterface;

final readonly class FileSchemaSource implements SchemaSourceInterface
{
    private string $path;

    public function __construct(string $path) {
        $this->path = $path;
    }

    public function load(): string
    {
        $chunks = [];

        foreach (glob($this->path . '/*.graphql') ?: [] as $file) {
            $chunks[] = trim(file_get_contents($file));
        }

        return implode("\n\n", $chunks);
    }
}
