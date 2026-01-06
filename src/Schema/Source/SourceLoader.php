<?php

namespace Netmex\Lumina\Schema\Source;

use Netmex\Lumina\Directives\Registry\DirectiveRegistry;

class SourceLoader
{
    private DirectiveRegistry $directives;
    private string $path;

    public function __construct(string $path, DirectiveRegistry $directives)
    {
        $this->path = $path;
        $this->directives = $directives;
    }

    public function load(): string
    {
        $chunks = array_merge(
            $this->loadFromDirectives(),
            $this->loadFromFiles()
        );

        return implode("\n\n", $chunks);
    }

    private function loadFromDirectives(): array
    {
        $chunks = [];
        foreach ($this->directives->all() as $name => $className) {
            $chunks[] = $className::definition();
        }
        return $chunks;
    }

    private function loadFromFiles(): array
    {
        $chunks = [];
        foreach (glob($this->path . '/*.graphql') ?: [] as $file) {
            $chunks[] = trim(file_get_contents($file));
        }
        return $chunks;
    }
}